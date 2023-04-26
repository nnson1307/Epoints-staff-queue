## MyStore Queue

### 1. Kết nối database
Đối với database local (không sử dụng kết nối ssl), nhớ thêm vào `.env` dòng sau:

```
DB_SSL=false
```

### 2. Run Queue standard alone

```bash
php artisan queue:work --queue=noti,noti-high,import,export --sleep=3 --tries=1
```

### 3. Run queue with Horizon monitoring
```bash
php artisan horizon
```

Để cấu hình queue cho horizon, vào file <span style='color:red'>`config/horizon.php`</span> tùy theo môi trường của `APP_ENV`, horizon sẽ chạy supervisor tương ứng

```bash
...

'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['noti-high', 'import', 'export'],
            'balance' => 'simple',
            'processes' => 1,
            'tries' => 1,
        ],
        'supervisor-2' => [
            'connection' => 'redis',
            'queue' => ['noti'],
            'balance' => 'simple',
            'processes' => 3,
            'tries' => 1,
        ],
    ],

    'local' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['noti', 'noti-high', 'import', 'export'],
            'balance' => 'simple',
            'processes' => 3,
            'tries' => 1,
        ],
    ],
],

...
```

** Lưu ý: Horizon chỉ hỗ trợ linux. Có thể cài Laravel Homestead để test

### 4. Một số lệnh trong Homestead
Tất cả các lệnh nhớ mở Terminal ở folder chứa cấu hình của Homestead.

Start máy ảo:
````
vagrant up
````

Reload máy ảo khi thay đổi cấu hình mới như: thêm site, thêm tính năng, thay đổi phiên bản php mặc định của site,...
````
vagrant reload --provision
````

SSH vào máy ảo
````
vagrant up
````

Chạy composer với phiên bản php tùy chọn, VD: 7.3
````
php7.3 /usr/local/bin/composer required ...
````

Chạy php artisan với php tùy chọn:
````
php7.3 artisan key:gen
````

### 5. Tài liệu tham khảo:
- Laravel Homestead: [https://laravel.com/docs/5.8/homestead](https://laravel.com/docs/5.8/homestead)
- Laravel Horizon: [https://laravel.com/docs/5.8/horizon](https://laravel.com/docs/5.8/horizon)