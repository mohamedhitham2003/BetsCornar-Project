<?php

return [
    'title' => 'المنتجات',
    'create_title' => 'إضافة منتج',
    'edit_title' => 'تعديل المنتج',
    'products.title' => 'المنتجات',
    'fields' => [
        'name' => 'الاسم',
        'type' => 'النوع',
        'price' => 'السعر',
        'quantity' => 'الكمية',
        'track_stock' => 'تتبع المخزون',
        'low_stock_threshold' => 'حد المخزون المنخفض',
        'stock_status' => 'حالة المخزون',
        'is_active' => 'نشط',
        'notes' => 'ملاحظات',
        'status' => 'الحالة',
    ],

    'types' => [
        'product' => 'منتج',
        'service' => 'خدمة',
        'vaccination' => 'تطعيم',
    ],

    'statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'available' => 'متوفر',
        'low' => 'منخفض',
        'out_of_stock' => 'نفذ المخزون',
        'all' => 'الكل',
    ],

    'actions' => [
        'add' => 'إضافة منتج',
        'edit' => 'تعديل',
        'save' => 'حفظ',
        'delete' => 'حذف',
        'search' => 'بحث',
        'clear' => 'إعادة تعيين',
        'activate' => 'تفعيل',
        'deactivate' => 'إلغاء التفعيل',
    ],

    'filters' => [
        'search_placeholder' => 'ابحث بالاسم...',
        'all_types' => 'كل الأنواع',
        'all_statuses' => 'كل الحالات',
    ],

    'messages' => [
        'created' => 'تم إنشاء المنتج بنجاح.',
        'updated' => 'تم تحديث المنتج بنجاح.',
        'deleted' => 'تم حذف المنتج بنجاح.',
        'toggled' => 'تم تحديث حالة المنتج بنجاح.',
        'deactivated_instead_of_delete' => 'هذا المنتج مرتبط بسجلات سابقة، تم تعطيله بدلًا من حذفه.',
        'confirm_delete' => 'هل أنت متأكد من حذف هذا المنتج؟',
        'no_results' => 'لا توجد منتجات مطابقة.',
        'service_stock_note' => 'الخدمة لا تتتبع المخزون، لذلك يتم ضبط الكمية وحد المخزون تلقائيًا على 0.',
        'insufficient_stock' => 'الكمية غير كافية في المخزون لهذا المنتج.',
    ],
];
