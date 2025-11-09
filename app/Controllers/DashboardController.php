<?php
namespace App\Controllers;

use App\Models\Plan;

class DashboardController extends Controller
{
    public function welcome(): void { $this->view('dashboard/welcome'); }

    public function dashboard(): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $plans = (new Plan($this->app))->allForUser($uid);
        $this->view('dashboard/index', compact('plans'));
    }
}
