<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // تم الإضافة: جلب جميع المستخدمين مع أدوارهم
        $users = User::with('roles')->latest()->paginate(15);
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // تم الإضافة: جلب الأدوار المتاحة لعرضها في القائمة
        $roles = Role::pluck('name', 'name')->all();
        
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // تم الإضافة: التحقق من البيانات المدخلة بتنسيق عربي
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'role' => 'الدور',
        ]);

        // تم الإضافة: إنشاء الموظف وتعيين دوره
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')
            ->with('success', 'تم إضافة الموظف بنجاح.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // تم الإضافة: جلب الدور الحالي للمستخدم للتعديل
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->first();
        
        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // تم الإضافة: تحديث بيانات الموظف والصلاحية
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'role' => 'الدور',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        // تحديث كلمة المرور فقط إذا تم إدخالها
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')
            ->with('success', 'تم تحديث بيانات الموظف بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // تم الإضافة: منع الأدمن من حذف نفسه
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'لا يمكنك حذف حسابك الشخصي.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'تم حذف الموظف بنجاح.');
    }
}
