<?php
session_start(); // Start the session (assuming user authentication is already done)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $dataTransaksi = json_decode(file_get_contents('php://input'), true);

  // Sambungkan ke database (ganti dengan informasi koneksi Anda)
  $host = 'localhost';
  $username = 'root';
  $password = '';
  $database = 'toserba';

  $koneksi = new mysqli($host, $username, $password, $database);

  // Lakukan proses penyimpanan ke database (misalnya, tabel nota)
  // Lakukan perhitungan total harga dan simpan data nota

  // Assuming the id_member is available in the session after login
  $idMember = $_SESSION['id_member'];

  // Contoh: Simpan data nota ke tabel nota
  $totalHarga = 0;
  foreach ($dataTransaksi as $transaksi) {
    $idBarang = $transaksi['id_barang'];
    $jumlah = $transaksi['jumlah'];

    // Query untuk mendapatkan harga jual barang dari tabel barang
    $queryBarang = "SELECT harga_jual FROM barang WHERE id_barang = '$idBarang'";
    $resultBarang = $koneksi->query($queryBarang);

    if ($resultBarang->num_rows > 0) {
      $hargaJual = $resultBarang->fetch_assoc()['harga_jual'];
      $subtotal = $hargaJual * $jumlah;
      $totalHarga += $subtotal;
    }
  }

  $tanggalInput = date('Y-m-d');
  $periode = date('Y');

  // Query untuk menyimpan data nota
  $queryNota = "INSERT INTO nota (id_member, id_barang, jumlah, total, tanggal_input, periode) VALUES ";
  foreach ($dataTransaksi as $transaksi) {
    $idBarang = $transaksi['id_barang'];
    $jumlah = $transaksi['jumlah'];

    $queryNota .= "('$idMember', '$idBarang', '$jumlah', '$totalHarga', '$tanggalInput', '$periode'),";
  }

  $queryNota = rtrim($queryNota, ','); // Menghapus koma terakhir
  $resultNota = $koneksi->query($queryNota);

  if ($resultNota) {
    $idNota = $koneksi->insert_id; // ID nota yang baru saja di-generate

    // Kirim ID nota sebagai respons ke klien
    header('Content-Type: application/json');
    echo json_encode(array('id_nota' => $idNota));
  } else {
    // Kirim pesan error jika penyimpanan gagal
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode(array('message' => 'Gagal menyimpan transaksi.'));
  }

  // Tutup koneksi database
  $koneksi->close();
}
?>
