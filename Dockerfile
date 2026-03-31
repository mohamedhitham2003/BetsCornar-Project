# استخدام صورة PHP إصدار 8.2 المخصصة لسطر الأوامر
FROM php:8.2-cli

# تثبيت الحزم الأساسية للنظام المطلوبة لتشغيل امتداد GD وباقي الإضافات
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# ضبط إعدادات إضافة الصور GD لتشمل دعم ملفات freetype و jpeg
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# تثبيت إضافات PHP المطلوبة للمشروع: GD و PDO و MySQL و BCMath
RUN docker-php-ext-install -j$(nproc) gd pdo pdo_mysql bcmath zip

# نسخ وتثبيت مدير الحزم Composer في الحاوية
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تحديد مجلد العمل ليكون /app في الحاوية
WORKDIR /app

# نسخ كافة ملفات المشروع إلى مجلد العمل
COPY . .

# تثبيت تبعيات المشروع عن طريق كومبوزر وتجاهل حزم التطوير لتحسين الأداء
RUN composer install --no-dev --optimize-autoloader

# إنشاء كاش للإعدادات الخاص بـ Laravel
RUN php artisan config:cache

# إنشاء كاش لمسارات الموقع
RUN php artisan route:cache

# إنشاء كاش لواجهات Blade
RUN php artisan view:cache

# تشغيل المشروع والسماح بالوصول الخارجي مع استخدام منفذ Railway المُعطى تلقائياً (PORT)
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
