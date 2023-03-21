<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use App\Repositories\AdminRepository;

class AdminsController extends Controller
{
    public function index(AdminRepository $adminRepository)
    {
        $admins = $adminRepository->all();

        return view('admin.admins.index', compact('admins'));
    }
}