# Currency Input Formatter - Dokumentasi

## Fitur
Sistem format input uang otomatis yang memberikan format Rupiah dengan pemisah ribuan saat user mengetik.

## Cara Penggunaan

### 1. Pada HTML Input
Tambahkan atribut `data-currency` pada input yang ingin diformat:

```html
<input type="text" name="amount" data-currency placeholder="Masukkan jumlah" required>
```

### 2. Auto-Initialize
Semua input dengan atribut `data-currency` akan otomatis diformat saat halaman dimuat.

### 3. Manual Initialize (untuk dynamic content)
Jika Anda menambahkan input secara dinamis:

```javascript
const input = document.getElementById('myInput');
if (typeof initCurrencyInput === 'function') {
    initCurrencyInput(input);
}
```

## Cara Kerja

1. **Input Display**: User melihat format seperti "1.000.000"
2. **Hidden Input**: Sistem otomatis membuat hidden input dengan nilai numerik (1000000)
3. **Form Submit**: Hidden input yang dikirim ke server dengan nama field asli
4. **Auto-format**: Saat user mengetik, otomatis tambah pemisah ribuan

## Contoh

### Input User
```
User ketik: 1000000
Display: 1.000.000
Value kirim: 1000000
```

### Edit Mode
Saat edit, nilai otomatis diformat:
```
Value dari DB: 5000000
Display ke user: 5.000.000
```

## Styling
Input dengan currency formatter otomatis mendapat:
- Font monospace untuk alignment yang baik
- Font weight bold
- Placeholder "Rp 0"

## Files Modified
- `/assets/js/main.js` - Core formatter functions
- `/wallets.php` - Wallet balance input
- `/transactions.php` - Transaction amount input

## Helper Functions

### formatNumber(num)
Format angka dengan pemisah ribuan
```javascript
formatNumber(1000000) // "1.000.000"
```

### parseFormattedNumber(str)
Parse string format kembali ke number
```javascript
parseFormattedNumber("1.000.000") // 1000000
```

### formatCurrency(amount)
Format currency untuk display dengan prefix Rp
```javascript
formatCurrency(1000000) // "Rp 1.000.000"
```
