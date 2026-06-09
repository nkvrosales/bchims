<?php

namespace App\Controllers;

use App\Models\AuditModel;
use App\Models\SourceModel;
use App\Models\UserModel;

class Sources extends BaseController
{
    protected $sourceModel;
    protected $auditModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->sourceModel = new SourceModel();
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

        $data['title'] = 'Sources';
        $data['sources'] = $this->sourceModel
            ->where('status', 1)
            ->orderBy('supplier_name', 'ASC')
            ->findAll();

        return view('templates/header', $data)
             . view('sources/sources', $data)
             . view('templates/footer');
    }

    public function create()
    {
        if ($res = $this->checkAuth()) return $res;

        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            return redirect()->to('sources');
        }

        $rules = [
            'source_type'    => 'required|in_list[Supplier,Donation,Others]',
            'supplier_name'  => 'required|max_length[150]',
            'contact_person' => 'permit_empty|max_length[150]',
            'contact_number' => 'permit_empty|max_length[50]',
            'address'        => 'permit_empty',
        ];

        if ($this->validate($rules)) {
            $insertData = [
                'source_type'    => $this->request->getPost('source_type'),
                'supplier_name'  => $this->request->getPost('supplier_name'),
                'contact_person' => $this->request->getPost('contact_person') ?: null,
                'contact_number' => $this->request->getPost('contact_number') ?: null,
                'address'        => $this->request->getPost('address') ?: null,
            ];

            if ($this->sourceModel->insert($insertData)) {
                $this->auditModel->log_activity(
                    'CREATE_SOURCE',
                    'Sources',
                    "Created source: {$insertData['supplier_name']} ({$insertData['source_type']})."
                );

                session()->setFlashdata('success', 'Source successfully created!');
                return redirect()->to('sources');
            }

            session()->setFlashdata('modal_mode', 'create');
            session()->setFlashdata('modal_errors', '<li>An error occurred while creating the source.</li>');
            return redirect()->to('sources')->withInput();
        }

        session()->setFlashdata('modal_mode', 'create');
        session()->setFlashdata('modal_errors', $this->validator->listErrors());
        return redirect()->to('sources')->withInput();
    }

    public function edit($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('sources');
        }

        $source = $this->sourceModel->find($id);
        if (empty($source)) {
            session()->setFlashdata('error', 'Source not found.');
            return redirect()->to('sources');
        }

        if (strcasecmp($this->request->getMethod(), 'get') === 0) {
            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            return redirect()->to('sources');
        }

        $rules = [
            'source_type'    => 'required|in_list[Supplier,Donation,Others]',
            'supplier_name'  => 'required|max_length[150]',
            'contact_person' => 'permit_empty|max_length[150]',
            'contact_number' => 'permit_empty|max_length[50]',
            'address'        => 'permit_empty',
        ];

        if ($this->validate($rules)) {
            $updateData = [
                'source_type'    => $this->request->getPost('source_type'),
                'supplier_name'  => $this->request->getPost('supplier_name'),
                'contact_person' => $this->request->getPost('contact_person') ?: null,
                'contact_number' => $this->request->getPost('contact_number') ?: null,
                'address'        => $this->request->getPost('address') ?: null,
            ];

            if ($this->sourceModel->update($id, $updateData)) {
                $this->auditModel->log_activity(
                    'UPDATE_SOURCE',
                    'Sources',
                    "Updated source: {$updateData['supplier_name']} ({$updateData['source_type']}).",
                    $id
                );

                session()->setFlashdata('success', 'Source successfully updated!');
                return redirect()->to('sources');
            }

            session()->setFlashdata('modal_mode', 'edit');
            session()->setFlashdata('modal_edit_id', $id);
            session()->setFlashdata('modal_errors', '<li>An error occurred while updating the source.</li>');
            return redirect()->to('sources')->withInput();
        }

        session()->setFlashdata('modal_mode', 'edit');
        session()->setFlashdata('modal_edit_id', $id);
        session()->setFlashdata('modal_errors', $this->validator->listErrors());
        return redirect()->to('sources')->withInput();
    }

    public function archive($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('sources');
        }

        $source = $this->sourceModel->find($id);
        if (empty($source)) {
            session()->setFlashdata('error', 'Source not found.');
            return redirect()->to('sources');
        }

        if ($this->sourceModel->update($id, ['status' => 0])) {
            $this->auditModel->log_activity(
                'ARCHIVE_SOURCE',
                'Sources',
                "Archived source: {$source['supplier_name']}.",
                $id
            );
            session()->setFlashdata('success', 'Source successfully archived.');
        } else {
            session()->setFlashdata('error', 'An error occurred while archiving the source.');
        }

        return redirect()->to('sources');
    }

    public function restore($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('sources');
        }

        $source = $this->sourceModel->find($id);
        if (empty($source)) {
            session()->setFlashdata('error', 'Source not found.');
            return redirect()->to('sources');
        }

        if ($this->sourceModel->update($id, ['status' => 1])) {
            $this->auditModel->log_activity(
                'RESTORE_SOURCE',
                'Sources',
                "Restored source: {$source['supplier_name']}.",
                $id
            );
            session()->setFlashdata('success', 'Source successfully restored.');
        } else {
            session()->setFlashdata('error', 'An error occurred while restoring the source.');
        }

        return redirect()->to('sources');
    }

    public function delete($id = null)
    {
        if ($res = $this->checkAuth()) return $res;

        if (empty($id)) {
            return redirect()->to('sources');
        }

        $source = $this->sourceModel->find($id);
        if (empty($source)) {
            session()->setFlashdata('error', 'Source not found.');
            return redirect()->to('sources');
        }

        $db = \Config\Database::connect();
        $inUse = $db->table('central_supply')->where('source_id', $id)->countAllResults();

        if ($inUse > 0) {
            session()->setFlashdata('error', 'Cannot delete a source that is currently used by inventory stock.');
            return redirect()->to('sources');
        }

        if ($this->sourceModel->delete($id)) {
            $this->auditModel->log_activity(
                'DELETE_SOURCE',
                'Sources',
                "Deleted source: {$source['supplier_name']}.",
                $id
            );
            session()->setFlashdata('success', 'Source successfully deleted!');
        } else {
            session()->setFlashdata('error', 'An error occurred while deleting the source.');
        }

        return redirect()->to('sources');
    }
}
