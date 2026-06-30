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
            session()->setFlashdata('session_expired', 'Your session has expired due to inactivity. Please log in again.');
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

        $search = trim((string) $this->request->getGet('search'));

        $builder = $this->categoryModel->orderBy('category_code', 'ASC');

        // Only hide inactive/archived categories if no search is active
        if (empty($search)) {
            $builder->where('status', 1);
        }

        if (!empty($search)) {
            $builder->groupStart()
                    ->like('category_description', $search)
                    ->orLike('category_code', $search)
                    ->groupEnd();
        }

        $categories = $builder->findAll();

        $data['title']      = 'Categories';
        $data['categories'] = $categories;
        $data['search']     = $search;

        return view('templates/header', $data)
             . view('categories', $data)
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
            'category_description' => 'required|max_length[100]',
        ];

        if ($this->validate($rules)) {
            $insertData = [
                'category_code'        => strtoupper($this->request->getPost('category_code')),
                'category_description' => ucwords(strtolower($this->request->getPost('category_description'))),
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

            session()->setFlashdata('modal_mode', 'create');
            session()->setFlashdata('modal_errors', '<li>An error occurred while creating the category.</li>');
            return redirect()->to('categories')->withInput();
        }

        session()->setFlashdata('modal_mode', 'create');
        session()->setFlashdata('modal_errors', $this->validator->listErrors());
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
            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            return redirect()->to('categories');
        }

        $rules = [
            'category_code'        => "required|alpha_dash|max_length[10]|is_unique[category.category_code,category_id,{$id}]",
            'category_description' => 'required|max_length[100]',
        ];

        if ($this->validate($rules)) {
            $oldCat = $this->categoryModel->find($id);

            $updateData = [
                'category_code'        => strtoupper($this->request->getPost('category_code')),
                'category_description' => ucwords(strtolower($this->request->getPost('category_description'))),
            ];

            if ($this->categoryModel->update($id, $updateData)) {
                $changes = [];
                if (($oldCat['category_code'] ?? '') !== $updateData['category_code']) {
                    $changes[] = "Code: '{$oldCat['category_code']}' → '{$updateData['category_code']}'";
                }
                if (($oldCat['category_description'] ?? '') !== ($updateData['category_description'] ?? '')) {
                    $changes[] = "Name: '{$oldCat['category_description']}' → '{$updateData['category_description']}'";
                }
                $auditDesc = "Updated category: {$updateData['category_code']}.";
                if ($changes) {
                    $auditDesc .= ' Changes: ' . implode(', ', $changes);
                }
                $this->auditModel->log_activity(
                    'UPDATE_CATEGORY',
                    'Categories',
                    $auditDesc,
                    $id
                );

                session()->setFlashdata('success', 'Category successfully updated!');
                return redirect()->to('categories');
            }

            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            session()->setFlashdata('modal_errors', '<li>An error occurred while updating the category.</li>');
            return redirect()->to('categories')->withInput();
        }

        session()->setFlashdata('modal_mode', 'edit');
        session()->setFlashdata('modal_edit_id', $id);
        session()->setFlashdata('modal_errors', $this->validator->listErrors());
        return redirect()->to('categories')->withInput();
    }

    public function archive($id = null)
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

        if ($this->categoryModel->update($id, ['status' => 0])) {
            $this->auditModel->log_activity(
                'ARCHIVE_CATEGORY',
                'Categories',
                "Archived category: {$category['category_code']}.",
                $id
            );
            session()->setFlashdata('success', 'Category successfully archived.');
        } else {
            session()->setFlashdata('error', 'An error occurred while archiving the category.');
        }

        return redirect()->to('categories');
    }

    public function restore($id = null)
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

        if ($this->categoryModel->update($id, ['status' => 1])) {
            $this->auditModel->log_activity(
                'RESTORE_CATEGORY',
                'Categories',
                "Restored category: {$category['category_code']}.",
                $id
            );
            session()->setFlashdata('success', 'Category successfully restored.');
        } else {
            session()->setFlashdata('error', 'An error occurred while restoring the category.');
        }

        return redirect()->to('categories');
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
