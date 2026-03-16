<?php

return [
    'title' => 'التطعيمات',
    'fields' => [
        'customer_name' => 'اسم العميل',
        'customer_phone' => 'رقم الهاتف',
        'animal_type' => 'نوع الحيوان',
        'vaccine_name' => 'اسم التطعيم',
        'vaccination_date' => 'تاريخ التطعيم',
        'next_dose_date' => 'موعد الجرعة القادمة',
        'invoice_reference' => 'رقم الفاتورة',
        'whatsapp' => 'واتساب',
    ],
    'filters' => [
        'all' => 'الكل',
        'upcoming' => 'الجرعات القادمة',
        'past' => 'السابقة',
        'search_placeholder' => 'ابحث باسم العميل أو رقم الهاتف...',
    ],
    'actions' => [
        'search' => 'بحث',
        'clear' => 'مسح البحث',
    ],
    'messages' => [
        'no_vaccinations_found' => 'لم يتم العثور على أي تطعيمات مسجلة.',
    ],
];
