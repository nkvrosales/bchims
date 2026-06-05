<?php

namespace App\Controllers;

use App\Models\AuditModel;
use App\Models\CategoryModel;
use App\Models\UserModel;

class Categories extends BaseController
{
    protected $categoryModel;
    protected $auditModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->categoryModel = new CategoryModel();
        $this->auditModel = new AuditModel();
    }

    protected function checkAuth()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $userModel = new UserModel();
        $user = $userModel->get_user_by_id(session()->get('user_id'));
        if (empty($user)) {
            session()->destroy();
            return redirect()->to('auth/login');
        }

        if (!is_admin_role()) {
            return redirect()->to('dashboard');
        }

        return null;
    }

    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $data['title'] = 'Categories';
        $data['categories'] = $this->categoryModel
            ->orderBy('category_code', 'ASC')
            ->findAll();

        return view('templates/header', $data)
             . view('categories/index', $data)
             . view('templates/footer');
    }

    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            return redirect()->to('categories');
        }

        $rules = [
            'category_code'        => 'required|alpha_dash|max_length[10]|is_unique[category.category_code]',
            'category_description' => 'permit_empty|max_length[100]',
        ];

        if ($this->validate($rules)) {
            $insertData = [
                'category_code'        => strtoupper($this->request->getPost('category_code')),
                'category_description' => $this->request->getPost('category_description') ?: null,
            ];

            if ($this->categoryModel->insert($insertData)) {
                $this->auditModel->log_activity(
                    'CREATE_CATEGORY',
                    'Categories',
                    "Created category: {$insertData['category_code']}."
                );

                session()->setFlashdata('success', 'Category successfully created!');
                return redirect()->to('categories');
            }

            session()->setFlashdata('create_modal_open', true);
            session()->setFlashdata('create_validation_errors', '<li>An error occurred while creating the category.</li>');
            return redirect()->to('categories')->withInput();
        }

        session()->setFlashdata('create_modal_open', true);
        session()->setFlashdata('create_validation_errors', $this->validator->listErrors());
        return redirect()->to('categories')->withInput();
    }

    public function edit($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('categories');
        }

        $category = $this->categoryModel->find($id);
        if (empty($category)) {
            session()->setFlashdata('error', 'Category not found.');
            return redirect()->to('categories');
        }

        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            session()->setFlashdata('edit_modal_open_id', $id);
            return redirect()->to('categories');
        }

        $rules = [
            'category_code'        => "required|alpha_dash|max_length[10]|is_unique[category.category_code,category_id,{$id}]",
            'category_description' => 'permit_empty|max_length[100]',
        ];

        if ($this->validate($rules)) {
            $updateData = [
                'category_code'        => strtoupper($this->request->getPost('category_code')),
                'category_description' => $this->request->getPost('category_description') ?: null,
            ];

            if ($this->categoryModel->update($id, $updateData)) {
                $this->auditModel->log_activity(
                    'UPDATE_CATEGORY',
                    'Categories',
                    "Updated category: {$updateData['category_code']}.",
                    $id
                );

                session()->setFlashdata('success', 'Category successfully updated!');
                return redirect()->to('categories');
            }

            session()->setFlashdata('edit_modal_open_id', $id);
            session()->setFlashdata('edit_validation_errors', '<li>An error occurred while updating the category.</li>');
            return redirect()->to('categories')->withInput();
        }

        session()->setFlashdata('edit_modal_open_id', $id);
        session()->setFlashdata('edit_validation_errors', $this->validator->listErrors());
        return redirect()->to('categories')->withInput();
    }

    public function delete($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('categories');
        }

        $category = $this->categoryModel->find($id);
        if (empty($category)) {
            session()->setFlashdata('error', 'Category not found.');
            return redirect()->to('categories');
        }

        $db = \Config\Database::connect();
        $inUse = $db->table('inventory')->where('category_id', $id)->countAllResults()
            + $db->table('central_supply')->where('category_id', $id)->countAllResults();

        if ($inUse > 0) {
            session()->setFlashdata('error', 'Cannot delete a category that is currently used by inventory stock.');
            return redirect()->to('categories');
        }

        if ($this->categoryModel->delete($id)) {
            $this->auditModel->log_activity(
                'DELETE_CATEGORY',
                'Categories',
                "Deleted category: {$category['category_code']}.",
                $id
            );
            session()->setFlashdata('success', 'Category successfully deleted!');
        } else {
            session()->setFlashdata('error', 'An error occurred while deleting the category.');
        }

        return redirect()->to('categories');
    }
}
