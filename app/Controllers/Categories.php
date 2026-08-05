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
            $lastUser = $_COOKIE['last_username'] ?? null;
            if ($lastUser) {
                $um = new \App\Models\UserModel();
                $u = $um->get_user_by_username($lastUser);
                $this->auditModel->log_activity('LOGOUT', 'Auth', "User account logged out due to inactivity \"$lastUser\".", null, $u ? $u['user_id'] : null);
                setcookie('last_username', '', time() - 3600, '/');
            }
            if ($lastUser) session()->setFlashdata('session_expired', 'Your session has expired due to inactivity. Please log in again.');
            return redirect()->to('auth/login');
        }

        $userModel = new UserModel();
        $user = $userModel->get_user_by_id(session()->get('user_id'));
        if (empty($user)) {
            session()->destroy();
            return redirect()->to('auth/login');
        }

        // Enforce single-session login: reject sessions whose token no longer
        // matches the user's current DB token (terminated by a newer login).
        if (!validate_session_token($user)) {
            session()->destroy();
            setcookie('last_username', '', time() - 3600, '/');
            return redirect()->to('auth/login?reason=terminated');
        }

        if (!is_admin_role()) {
            return redirect()->to('dashboard');
        }

        return null;
    }

    public function index()
    {
        if ($res = $this->checkAuth()) return $res;

        $search        = trim((string) $this->request->getGet('search'));
        $status_filter = trim((string) $this->request->getGet('status_filter'));

        $categories = $this->categoryModel->search_categories($search, $status_filter);

        $data['title']        = 'Categories';
        $data['categories']   = $categories;
        $data['search']       = $search;
        $data['status_filter'] = $status_filter;

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
            'category_name' => 'required|max_length[100]',
        ];

        if ($this->validate($rules)) {
            $insertData = [
                'category_code'        => strtoupper($this->request->getPost('category_code')),
                'category_name' => ucwords(strtolower($this->request->getPost('category_name'))),
            ];

            if ($this->categoryModel->insert($insertData)) {
                $this->auditModel->log_activity(
                    'CREATE_CATEGORY',
                    'Categories',
                    "Created category: {$insertData['category_code']}."
                );

                session()->setFlashdata('success', 'Category successfully created.');
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
            'category_name' => 'required|max_length[100]',
        ];

        if ($this->validate($rules)) {
            $oldCat = $this->categoryModel->find($id);

            $updateData = [
                'category_code'        => strtoupper($this->request->getPost('category_code')),
                'category_name' => ucwords(strtolower($this->request->getPost('category_name'))),
            ];

            if ($this->categoryModel->update($id, $updateData)) {
                $changes = [];
                if (($oldCat['category_code'] ?? '') !== $updateData['category_code']) {
                    $changes[] = "Code: '{$oldCat['category_code']}' → '{$updateData['category_code']}'";
                }
                if (($oldCat['category_name'] ?? '') !== ($updateData['category_name'] ?? '')) {
                    $changes[] = "Name: '{$oldCat['category_name']}' → '{$updateData['category_name']}'";
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

                session()->setFlashdata('success', 'Category successfully updated.');
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

    public function deactivate($id = null)
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
                'DEACTIVATE_CATEGORY',
                'Categories',
                "Deactivated category: {$category['category_code']}.",
                $id
            );
            session()->setFlashdata('success', 'Category successfully deactivated.');
        } else {
            session()->setFlashdata('error', 'An error occurred while deactivating the category.');
        }

        return redirect()->to('categories');
    }

    public function reactivate($id = null)
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
                'REACTIVATE_CATEGORY',
                'Categories',
                "Reactivated category: {$category['category_code']}.",
                $id
            );
            session()->setFlashdata('success', 'Category successfully reactivated.');
        } else {
            session()->setFlashdata('error', 'An error occurred while reactivating the category.');
        }

        return redirect()->to('categories');
    }

}
