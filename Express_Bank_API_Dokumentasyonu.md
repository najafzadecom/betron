# Express Bank API Dokümantasyonu

## İçindekiler
1. [Genel Bilgiler](#genel-bilgiler)
2. [Kimlik Doğrulama](#kimlik-doğrulama)
3. [API Endpoints](#api-endpoints)
   - [Banka Listesi](#banka-listesi)
   - [Hesap Bilgileri](#hesap-bilgileri)
   - [İşlem Oluşturma](#işlem-oluşturma)
   - [Para Çekme](#para-çekme)
4. [Hata Kodları](#hata-kodları)
5. [Örnek Kullanım](#örnek-kullanım)

---

## Genel Bilgiler

**API Base URL:** `{{api_url}}`  
**API Versiyonu:** v1  
**İçerik Tipi:** application/json  
**Kimlik Doğrulama:** Bearer Token  

### Genel Response Formatı
Tüm API yanıtları aşağıdaki genel formatı takip eder:

```json
{
    "success": boolean,
    "code": integer,
    "message": "string",
    "data": object|array,
    "total": integer (opsiyonel)
}
```

---

## Kimlik Doğrulama

Tüm API istekleri Bearer Token ile kimlik doğrulaması gerektirir.

**Header Formatı:**
```
Authorization: Bearer {{token}}
```

---

## API Endpoints

### 1. Banka Listesi

Sistemde kayıtlı tüm bankaların listesini getirir.

**Endpoint:** `GET /bank`  
**Kimlik Doğrulama:** Gerekli  
**Parametreler:** Yok  

#### Request Örneği
```http
GET {{api_url}}/bank
Authorization: Bearer {{token}}
```

#### Response Örneği
```json
{
    "success": true,
    "message": "Banks retrieved successfully",
    "code": 200,
    "total": 25,
    "data": [
        {
            "id": 1,
            "name": "Bank Express",
            "image": "https://95.217.97.119/storage/uploads/banks/1754504765_68939e3d830df.png"
        },
        {
            "id": 3,
            "name": "Cremin, Stehr and Larson",
            "image": "https://95.217.97.119/storage/https://via.placeholder.com/640x480.png/0000aa?text=business+Bank+culpa"
        }
    ]
}
```

#### Response Alanları
- `id`: Banka benzersiz kimliği
- `name`: Banka adı
- `image`: Banka logosu URL'i
- `total`: Toplam banka sayısı

---

### 2. Hesap Bilgileri (Wallet)

Belirtilen miktar için uygun wallet bilgilerini getirir.

**Endpoint:** `GET /wallet`  
**Kimlik Doğrulama:** Gerekli  
**Parametreler:** 
- `amount` (query, gerekli): İşlem miktarı

#### Request Örneği
```http
GET {{api_url}}/wallet?amount=1000
Authorization: Bearer {{token}}
```

#### Response Örneği
```json
{
    "success": true,
    "code": 200,
    "message": "Wallet retrieved successfully",
    "data": {
        "id": 1,
        "name": "ACC-8000-9400",
        "iban": "TR957905179U8BWXJA5F501MD8"
    }
}
```

#### Response Alanları
- `id`: Wallet benzersiz kimliği
- `name`: Hesap adı/numarası
- `iban`: IBAN numarası

---

### 3. İşlem Oluşturma

Yeni bir para transferi işlemi oluşturur.

**Endpoint:** `POST /transaction`  
**Kimlik Doğrulama:** Gerekli  
**Content-Type:** application/json  

#### Request Parametreleri
| Alan | Tip | Gerekli | Açıklama |
|------|-----|---------|----------|
| user_id | integer | Evet | Kullanıcı kimliği |
| first_name | string | Evet | Ad |
| last_name | string | Evet | Soyad |
| phone | string | Evet | Telefon numarası |
| amount | float | Evet | İşlem miktarı |
| bank_name | string | Evet | Banka adı |
| bank_id | integer | Evet | Banka kimliği |
| iban | string | Evet | IBAN numarası |
| wallet_id | integer | Evet | Wallet kimliği |
| client_ip | string | Evet | İstemci IP adresi |

#### Request Örneği
```http
POST {{api_url}}/transaction
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "user_id": 1,
    "first_name": "Kamran",
    "last_name": "Najafzade",
    "phone": "+994501234567",
    "amount": 100.50,
    "bank_name": "Kapital Bank",
    "bank_id": 1,
    "iban": "AZ21NABZ00000000137010001944",
    "wallet_id": 2,
    "client_ip": "192.168.1.1"
}
```

#### Response Örneği
```json
{
    "success": true,
    "code": 201,
    "message": "Transaction created successfully",
    "data": {
        "first_name": "Kamran",
        "last_name": "Najafzade",
        "phone": "+994501234567",
        "amount": 100.5,
        "bank_name": "Kapital Bank",
        "bank_id": 1,
        "iban": "AZ21NABZ00000000137010001944",
        "wallet_id": 2,
        "client_ip": "192.168.1.1",
        "site_id": 1,
        "updated_at": "2025-08-07T00:05:36.000000Z",
        "created_at": "2025-08-07T00:05:36.000000Z",
        "id": 11,
        "sender": "Kamran Najafzade",
        "receiver": "ACC-0026-6557 (TR26441621NR68RLO79SWE15YD)",
        "site_name": "1xBet",
        "wallet": {
            "id": 2,
            "current_account": "ACC-0026-6557",
            "iban": "TR26441621NR68RLO79SWE15YD",
            "total_amount": 89198.03,
            "blocked_amount": 455.83,
            "last_sync_date": "2025-07-28T13:09:23.000000Z",
            "bank": "Trantow-Wilderman Bank",
            "description": "Nihil incidunt ea mollitia alias hic est fugit esse occaecati est aut hic.",
            "status": false,
            "currency": "TRY",
            "created_at": "2025-08-05T21:00:29.000000Z",
            "updated_at": "2025-08-05T21:00:29.000000Z",
            "deleted_at": null
        }
    }
}
```

---

### 4. Para Çekme

Yeni bir para çekme talebi oluşturur.

**Endpoint:** `POST /withdrawal`  
**Kimlik Doğrulama:** Gerekli  
**Content-Type:** application/json  

#### Request Parametreleri
| Alan | Tip | Gerekli | Açıklama |
|------|-----|---------|----------|
| user_id | integer | Evet | Kullanıcı kimliği |
| first_name | string | Evet | Ad |
| last_name | string | Evet | Soyad |
| amount | float | Evet | Çekilecek miktar |
| bank_name | string | Evet | Banka adı |
| iban | string | Evet | IBAN numarası |
| order_id | integer | Evet | Sipariş kimliği |

#### Request Örneği
```http
POST {{api_url}}/withdrawal
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "user_id": 1,
    "first_name": "Kamran",
    "last_name": "Najafzade",
    "amount": 100.50,
    "bank_name": "Kapital Bank",
    "iban": "AZ21NABZ00000000137010001944",
    "order_id": 2
}
```

#### Response Örneği
```json
{
    "success": true,
    "code": 201,
    "message": "Withdrawal created successfully",
    "data": {
        "first_name": "Kamran",
        "last_name": "Najafzade",
        "amount": 100.5,
        "bank_name": "Kapital Bank",
        "iban": "AZ21NABZ00000000137010001944",
        "order_id": 2,
        "site_id": 1,
        "user_id": 1,
        "updated_at": "2025-08-07T00:23:32.000000Z",
        "created_at": "2025-08-07T00:23:32.000000Z",
        "id": 5,
        "status_html": "<span class=\"badge bg-dark\"></span>",
        "receiver": "Kamran Najafzade",
        "sender": " ",
        "wallet": null
    }
}
```

---

## Hata Kodları

| HTTP Kodu | Açıklama |
|-----------|----------|
| 200 | Başarılı |
| 201 | Oluşturuldu |
| 400 | Geçersiz İstek |
| 401 | Yetkisiz |
| 403 | Erişim Engellendi |
| 404 | Bulunamadı |
| 422 | Doğrulama Hatası |
| 500 | Sunucu Hatası |

### Hata Response Formatı
```json
{
    "success": false,
    "code": 400,
    "message": "Hata mesajı",
    "errors": {
        "field_name": ["Hata detayı"]
    }
}
```

---

## Örnek Kullanım

### 1. Tam İşlem Akışı

```javascript
// 1. Banka listesini al
const banksResponse = await fetch('{{api_url}}/bank', {
    headers: {
        'Authorization': 'Bearer {{token}}'
    }
});

// 2. Wallet bilgilerini al
const walletResponse = await fetch('{{api_url}}/wallet?amount=1000', {
    headers: {
        'Authorization': 'Bearer {{token}}'
    }
});

// 3. İşlem oluştur
const transactionResponse = await fetch('{{api_url}}/transaction', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer {{token}}',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        user_id: 1,
        first_name: "Ahmet",
        last_name: "Yılmaz",
        phone: "+905551234567",
        amount: 1000,
        bank_name: "Ziraat Bankası",
        bank_id: 1,
        iban: "TR330006100519786457841326",
        wallet_id: 2,
        client_ip: "192.168.1.100"
    })
});
```

---

## Güvenlik Notları

1. **Token Güvenliği:** Bearer token'ı güvenli bir şekilde saklayın
2. **HTTPS:** Tüm API çağrıları HTTPS üzerinden yapılmalıdır
3. **IP Kontrolü:** client_ip parametresi doğru IP adresini içermelidir
4. **Veri Doğrulama:** Tüm giriş verileri sunucu tarafında doğrulanır

---

## Destek

API ile ilgili sorularınız için:
- **E-posta:** support@expressbank.com
- **Telefon:** +90 212 555 0000
- **Dokümantasyon Güncellenme Tarihi:** Ağustos 2025

---

*Bu dokümantasyon Express Bank API v1.0 için hazırlanmıştır.*
