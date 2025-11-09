<?php
namespace App\Controllers;

use App\Models\Position;

class PositionsController extends Controller
{
    public function index(): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $positions = (new Position($this->app))->allOngoing($uid);
        $this->view('positions/index', compact('positions'));
    }

    public function show(int $id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $pos = (new Position($this->app))->find($id, $uid);
        if (!$pos) { http_response_code(404); echo 'Not Found'; return; }
        $this->view('positions/show', compact('pos'));
    }

    public function adjust(int $id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $sl = (float)($_POST['stop_loss'] ?? 0);
        $tp = (float)($_POST['target_price'] ?? 0);
        (new Position($this->app))->updateStops($id, $uid, $sl, $tp);
        $this->app->flash->success('Stops updated.');
        $this->redirect('/app/positions/'.$id);
    }

    public function partial(int $id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $qty   = (float)($_POST['qty'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $fees  = (float)($_POST['fees'] ?? 0);
        (new Position($this->app))->partialClose($id, $uid, $qty, $price, $fees);
        $this->app->flash->success('Partial close recorded.');
        $this->redirect('/app/positions/'.$id);
    }

    public function close(int $id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $price = (float)($_POST['price'] ?? 0);
        $fees  = (float)($_POST['fees'] ?? 0);
        (new Position($this->app))->fullClose($id, $uid, $price, $fees);
        $this->app->flash->success('Position closed.');
        $this->redirect('/app/positions/'.$id);
    }
}
