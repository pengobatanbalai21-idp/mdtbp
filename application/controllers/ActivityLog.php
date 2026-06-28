<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ActivityLog extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(['admin', 'pimpinan']);
        $this->load->model('Activity_log_model');
    }

    public function index()
    {
        $search = trim($this->input->get('search') ?? '');
        $action = trim($this->input->get('action') ?? '');

        $perPage = 20;
        $page    = max(1, (int)($this->input->get('page') ?? 1));
        $offset  = ($page - 1) * $perPage;

        $logs    = $this->Activity_log_model->getFiltered($search, $action, $perPage, $offset);
        $total   = $this->Activity_log_model->countFiltered($search, $action);
        $actions = $this->Activity_log_model->getActionList();

        $data = $this->viewData([
            'title'   => 'Log Aktivitas',
            'logs'    => $logs,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => max(1, (int)ceil($total / $perPage)),
            'search'  => $search,
            'action'  => $action,
            'actions' => $actions,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('activity_log/index', $data);
        $this->load->view('templates/footer', $data);
    }
}
