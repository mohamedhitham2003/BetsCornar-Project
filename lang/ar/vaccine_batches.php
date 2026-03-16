<?php

return [
    'title' => 'دفعات اللقاحات',
    'create_title' => 'إضافة دفعة لقاح',
    'edit_title' => 'تعديل دفعة لقاح',

    'fields' => [
        'product' => 'اللقاح',
        'batch_code' => 'كود الدفعة',
        'received_date' => 'تاريخ الاستلام',
        'expiry_date' => 'تاريخ الانتهاء',
        'quantity_received' => 'الكمية المستلمة',
        'quantity_remaining' => 'الكمية المتبقية',
        'status' => 'الحالة',
    ],

    'statuses' => [
        'usable' => 'صالحة',
        'expired' => 'منتهية',
        'expiring_soon' => 'تنتهي قريباً',
    ],

    'actions' => [
        'add' => 'إضافة دفعة',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'save' => 'حفظ',
        'search' => 'بحث',
        'clear' => 'إعادة تعيين',
    ],

    'filters' => [
        'search_placeholder' => 'ابحث بكود الدفعة أو اسم اللقاح...',
        'all_vaccines' => 'كل اللقاحات',
    ],

    'messages' => [
        'created' => 'تمت إضافة دفعة اللقاح بنجاح.',
        'updated' => 'تم تحديث دفعة اللقاح بنجاح.',
        'deleted' => 'تم حذف دفعة اللقاح بنجاح.',
        'delete_referenced_error' => 'لا يمكن حذف دفعة لقاح لها سجل استخدام سابق.',
        'confirm_delete' => 'هل أنت متأكد من حذف دفعة اللقاح؟',
        'no_results' => 'لا توجد دفعات لقاح مطابقة.',
        'insufficient_stock' => 'لا توجد كمية صالحة كافية من اللقاح (حسب تواريخ الانتهاء).',
        'remaining_hint' => 'يمكن ترك الكمية المتبقية فارغة لإدخال نفس الكمية المستلمة تلقائياً عند الإنشاء.',
        'remaining_exceeds_received' => 'الكمية المتبقية لا يمكن أن تكون أكبر من الكمية المستلمة.',
        'invalid_vaccine_product' => 'يجب اختيار منتج لقاح فعّال مع تتبع مخزون.',
    ],
];