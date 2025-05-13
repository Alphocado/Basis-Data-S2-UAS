// assets/js/script.js

// Fungsi untuk validasi form
function validateForm() {
    // Contoh validasi sederhana
    const inputs = document.querySelectorAll('form input, form select');
    let isValid = true;

    inputs.forEach(input => {
        if (input.hasAttribute('required') && !input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
}

// Tambahkan event listener untuk validasi form
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateForm()) {
                e.preventDefault(); // Mencegah pengiriman form jika tidak valid
            }
        });
    });

    // Fungsi untuk menampilkan konfirmasi sebelum aksi penting
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });

    // Fungsi untuk membuka/menutup menu navigasi di mobile
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.navbar-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }
});

// Fungsi untuk memformat rupiah
function formatRupiah(angka) {
    const numberString = angka.toString();
    const split = numberString.split(',');
    const sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        const separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    return 'Rp ' + rupiah + (split[1] ? ',' + split[1] : '');
}

// Fungsi untuk menghitung denda
function hitungDenda(tanggalPinjam, tanggalKembali, dendaPerHari) {
    const pinjam = new Date(tanggalPinjam);
    const kembali = new Date(tanggalKembali);
    const selisihHari = Math.max(0, Math.floor((kembali - pinjam) / (1000 * 60 * 60 * 24)) - 7); // Asumsi batas peminjaman 7 hari
    
    return selisihHari * dendaPerHari;
}