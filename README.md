# Simple-Recaptcha

<h2>環境</h2>
- php5以上
- curl

<h2>操作流程</h2>

1. 設置參數

```php
//預設參數
$config = [
  'siteKey' => '', //金鑰
  'secretKey' => '' //密鑰
];
$Recaptcha = new Recaptcha($config);
```

2. 在頁面崁入tag 跟 js，可以使用getWidget() 跟 getScriptTag()

3. 當點擊後會得到回傳值 g-recaptcha-response，連同表單一起送出處理

4. 將得到得值做驗證，使用 verifyResponse()
