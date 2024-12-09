const moment = require('moment-timezone');

// Set timezone default untuk seluruh aplikasi
moment.tz.setDefault('Asia/Jakarta');

const express = require('express');
const bodyParser = require('body-parser');
const bcrypt = require('bcryptjs');
const { body, validationResult } = require('express-validator');
const cors = require('cors');
const mysql = require('mysql2');
const multer = require('multer');
const fs = require('fs');
const csv = require('csv-parser');
const XLSX = require('xlsx');
const ExcelJS = require('exceljs');
//module.exports = pool.promise();
var i=0;

const path = require('path');
const app = express();
const port = 3000;

// Konfigurasi penyimpanan untuk Multer
const upload = multer({ dest: 'uploads/' });

// Middleware
app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());
app.use(cors());
app.use(express.json());

//const mysql = require('mysql');
let db;

function connectDatabase() {
    // Setup koneksi MySQL
    db = mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: 'root',  // Ganti dengan password MySQL Anda
        database: 'bonstok',
        waitForConnections: true,
        keepAliveInitialDelay: 50000,
        enableKeepAlive: true,
        //connectionLimit: 10,
        //queueLimit: 0
    });

    db.connect((err) => {
        if (err) {
            console.error('Error connecting:', err);
            setTimeout(connectDatabase, 2000); // coba reconnect
        }
    });

    db.on('error', (err) => {
        if (err.code === 'PROTOCOL_CONNECTION_LOST') {
            connectDatabase(); // reconnect saat koneksi terputus
        } else {
            throw err;
        }
    });
}

connectDatabase();

// test koneksi ke database
db.connect(err => {
    if (err) throw err;
    console.log('MySQL Connected...');
});



//================================================================================================

// tampilkan pencarian data impor
app.get('/imporbcas', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT tbl_impor_bca.tgl_transaksi AS tgl_bayar, tbl_impor_bca.keterangan, CAST(REPLACE(LEFT(tbl_impor_bca.jumlah, LENGTH(tbl_impor_bca.jumlah) - 3), ',', '') AS DECIMAL(13,2)) AS nilai_bayar, tbl_beli_main.no_beli, tbl_beli_main.tgl_beli,  tbl_beli_main.tot_beli AS total_beli, tbl_beli_main.tot_beli - CAST(REPLACE(LEFT(tbl_impor_bca.jumlah, LENGTH(tbl_impor_bca.jumlah) - 3), ',', '') AS DECIMAL(13,2)) AS sisa_hutang FROM tbl_impor_bca INNER JOIN tbl_beli_main ON  tbl_impor_bca.keterangan LIKE CONCAT('%', tbl_beli_main.no_beli, '%') WHERE tbl_impor_bca.keterangan like ? OR tbl_impor_bca.tgl_transaksi like ? ORDER BY tbl_impor_bca.tgl_transaksi;";
  const values = [`%${searchQuery}%`,`%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// API untuk menerima unggahan file CSV
app.post('/imporbcas', upload.single('file'), (req, res) => {
    const file = req.file;
    const tgl = req.body.tgl_transaksi;

    if (!file) {
        return res.status(400).send('Tidak ada file yang diunggah.');
    }

    // Baca file CSV dan impor ke MySQL
    const filePath = file.path;

    fs.createReadStream(filePath)
    .pipe(csv())
    .on('data', (row) => {
        // Sesuaikan kolom dengan tabel MySQL
        const query = 'INSERT IGNORE INTO tbl_impor_bca(tgl_transaksi, tgl_bca, keterangan, cabang, jumlah, saldo) VALUES (?, ?, ?, ?, ?, ?)';

        db.query(query, [tgl, row.tgl_bca, row.keterangan, row.cabang, row.jumlah, row.saldo], (err, result) => {
            if (err) {
                console.error('Gagal menyimpan data:', err);
            } else {
                console.log('Data berhasil disimpan:', result);
            }
        });
    })
    .on('end', () => {
        // Hapus file setelah diproses
        fs.unlinkSync(filePath);
        res.send('File CSV berhasil diunggah dan diimpor ke MySQL.');
    });
});



// function generate No Bayar
const generateNoBayar = async (tglbayar,count) => {
    const tgl_bayar = new Date(tglbayar);

    const yearMonth = 
        tgl_bayar.getFullYear().toString().slice(2) + 
        (tgl_bayar.getMonth() + 1).toString().padStart(2, '0');

    const sqlGetLast = `
        SELECT no_bayar 
        FROM tbl_bayar_main 
        WHERE no_bayar LIKE 'BY${yearMonth}%' 
        ORDER BY no_bayar DESC 
        LIMIT 1
    `;

    try {
        // Eksekusi query untuk mendapatkan nomor bayar terakhir
        const results = await executeQuery(sqlGetLast);

        // Tambahkan pengecekan jika results tidak valid
        if (!results || !Array.isArray(results)) {
            throw new Error('Invalid query result');
        }

        let newNoBayar = '';
    
        if (results.length > 0) {
            const lastNo = results[0]?.no_bayar || '';
            const kodeNumber = parseInt(lastNo.replace('BY' + yearMonth, '')) + count;
            newNoBayar = 'BY' + yearMonth + kodeNumber.toString().padStart(4, '0');
        } else {
            newNoBayar = 'BY' + yearMonth + count.toString().padStart(4, '0');
        }

        return newNoBayar;

    } catch (error) {
        console.error('Error generating no_bayar:', error);
        throw new Error('Database error during no_bayar generation.');
    }
};


const executeQuery = (sql, params = []) => {
    return new Promise((resolve, reject) => {
        db.query(sql, params, (err, results) => {
            if (err) {
                console.error('Database query error:', err);
                return reject(err);
            }
            resolve(results);
        });
    });
};




// API untuk posting unggahan file CSV ke database
app.put('/imporbcas', async (req, res) => {
    const metodepay = req.body.kd_metode_bayar;

    const sqlQuery = `
        SELECT 
            tbl_impor_bca.tgl_transaksi AS tgl_bayar, 
            tbl_impor_bca.keterangan, 
            CAST(REPLACE(LEFT(tbl_impor_bca.jumlah, LENGTH(tbl_impor_bca.jumlah) - 3), ',', '') AS DECIMAL(13,2)) AS nilai_bayar, 
            tbl_beli_main.no_beli, 
            tbl_beli_main.tgl_beli, 
            tbl_beli_main.kd_supplier, 
            tbl_beli_main.tot_beli AS total_beli, 
            tbl_beli_main.tot_beli - CAST(REPLACE(LEFT(tbl_impor_bca.jumlah, LENGTH(tbl_impor_bca.jumlah) - 3), ',', '') AS DECIMAL(13,2)) AS sisa_hutang 
        FROM 
            tbl_impor_bca 
        INNER JOIN 
            tbl_beli_main 
        ON 
            tbl_impor_bca.keterangan LIKE CONCAT('%', tbl_beli_main.no_beli, '%') 
        ORDER BY 
            tbl_impor_bca.tgl_transaksi;
    `;

    try {
        // Eksekusi query untuk mendapatkan data
        const results = await new Promise((resolve, reject) => {
            db.query(sqlQuery, (err, results) => {
                if (err) return reject(err);
                resolve(results);
            });
        });

        // Pastikan hasil query tidak kosong
        if (results.length === 0) {
            return res.status(404).json({ message: 'No transactions found.' });
        }


        const sqlInsert = 'INSERT IGNORE INTO tbl_bayar_main SET ?';
        const sqlInsertBeli = 'INSERT INTO tbl_bayar_detail SET ?';

        // Loop melalui setiap hasil query dan simpan ke tabel tbl_bayar_main
        const insertPromises = results.map(async (transaction) => {
            try {

                //counter data
                i++;

                const newNoBayar = await generateNoBayar(transaction.tgl_bayar,i); // Tunggu nomor bayar
                

                const newItem = {
                    tgl_bayar: transaction.tgl_bayar,
                    kd_supplier: transaction.kd_supplier,
                    ket_bayar: transaction.keterangan,
                    kd_metode_bayar: metodepay,
                    total_bayar: transaction.nilai_bayar,
                    user_created: req.body.useraktif || 'default_user',
                    date_created: new Date(),
                    no_bayar: newNoBayar
                };

                // Lakukan INSERT data ke tbl_bayar_main
                await new Promise((resolve, reject) => {
                    db.query(sqlInsert, newItem, (err, result) => {
                        if (err) return reject(err);
                        resolve(result);
                    });
                });

                const newItemBeli = {
                    no_beli: transaction.no_beli,
                    nilai_bayar: transaction.nilai_bayar,
                    user_created: req.body.useraktif || 'default_user',
                    date_created: new Date(),
                    no_bayar: newNoBayar
                };

                // Lakukan INSERT ke tbl_bayar_detail
                await new Promise((resolve, reject) => {
                    db.query(sqlInsertBeli, newItemBeli, (err, result) => {
                        if (err) return reject(err);
                        resolve(result);
                    });
                });

            } catch (error) {
                console.error('Error during transaction processing:', error);
                throw error;
            }
        });

        i=0;

        // Tunggu hingga semua transaksi selesai diproses
        await Promise.all(insertPromises);

        // Kirim response setelah semua transaksi selesai diproses
        res.json({ message: 'Transactions have been uploaded successfully!' });

    } catch (err) {
        console.error('Error processing transactions:', err);
        res.status(500).json({ message: 'Error processing transactions.' });
    }
});


// hapus data impor bca
app.delete('/imporbcas', (req, res) => {
    const sql = 'DELETE tbl_impor_bca.* FROM tbl_impor_bca';
    db.query(sql, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


// Endpoint API untuk mengekspor data ke Excel
app.get('/beliexport', (req, res) => {
    
    const searchQuery = req.query.q || ''; // Default kosong jika tidak ada query
    //console.log('Query Pencarian:', searchQuery);

    const query = "SELECT tbl_beli_main.no_beli, tbl_beli_main.tgl_beli, tbl_supplier.nm_supplier, tbl_beli_main.tot_beli, IFNULL(tbl_bayar_detail.nilai_bayar, 0) AS tot_bayar, tbl_beli_main.tot_beli - IFNULL(tbl_bayar_detail.nilai_bayar, 0) AS tot_hutang FROM tbl_beli_main LEFT JOIN tbl_supplier ON tbl_beli_main.kd_supplier = tbl_supplier.kd_supplier LEFT JOIN tbl_bayar_detail ON tbl_beli_main.no_beli = tbl_bayar_detail.no_beli WHERE tbl_beli_main.no_beli LIKE ? OR tbl_beli_main.tgl_beli LIKE ? OR tbl_supplier.nm_supplier LIKE ? ORDER BY tbl_beli_main.no_beli DESC";

    //console.log('Query:', query);
    const values = [`%${searchQuery}%`, `%${searchQuery}%`, `%${searchQuery}%`];
    
    // Perbaikan di sini: Pastikan hasil query benar
    db.query(query, values, (error, results) => {
        if (error) return res.status(500).send('Error menjalankan query.');

        // Konversi hasil query ke worksheet
        const worksheet = XLSX.utils.json_to_sheet(results);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Data');

        // Simpan file Excel di server sementara
        const filePath = path.join(__dirname, 'data.xlsx'); // Path ditentukan di sini
        XLSX.writeFile(workbook, filePath);

        // Kirim file Excel sebagai respon
        res.download(filePath, 'data.xlsx', (err) => {
            if (err) console.error(err);

            // Hapus file setelah dikirim ke client
            fs.unlinkSync(filePath);
        });
    });
});



//================================================================================================

// tampilkan pencarian data user
app.get('/users', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT * FROM tbl_user WHERE user_name LIKE ? ";
  const values = [`%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tampilkan pencarian data user
app.get('/userlogin', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT * FROM tbl_user WHERE user_name LIKE ? AND is_aktif=1";
  const values = [`%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data user baru
app.post('/users', (req, res) => {

    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const newItem = { user_name: req.body.user_name, password: req.body.password, is_aktif: req.body.is_aktif, user_created: req.body.useraktif, date_created:now };
    const sql = 'INSERT INTO tbl_user SET ?';
    db.query(sql, newItem, (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        if (err) throw err;
        res.json({ id: result.insertId, ...newItem });
    });

});

// update data user
app.put('/users/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { password, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_user SET password=?, user_modified=?, date_modified=NOW() WHERE id_user = ?";
    db.query(sql, [password, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// aktif/non aktifkan user
app.delete('/users', (req, res) => {
    const id = req.query.delete;
    const isaktif = req.query.isaktif;
    const sql = 'UPDATE tbl_user SET is_aktif=? WHERE id_user = ?';
    db.query(sql, [isaktif, id], (err, result) => {
        if (err) throw err;
        res.json({ message: 'User aktif/non aktif succesed' });
    });
});



//================================================================================================

// tampilkan pencarian data user permission
app.get('/permissions', (req, res) => {

  const username = req.query.user;
  const menuname = req.query.menu;
  const query = "SELECT * FROM tbl_userpermission WHERE user_name=? AND menu_name=?";
  const values = [username, menuname];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// copy semua permission seperti tbl_task sebagai default
app.post('/permissions', (req, res) => {

  const { username, useraktif } = req.body
  const query = "INSERT IGNORE INTO tbl_userpermission(user_name, menu_name, is_open, is_addnew, is_update, is_delete, user_created, date_created) SELECT ? AS user_name, tbl_task.menu_name, tbl_task.is_open, tbl_task.is_addnew, tbl_task.is_update, tbl_task.is_delete, ?, Now() FROM tbl_task";
  const values = [username, useraktif];

  db.query(query, values, (err, results) => {
        if (err) {
          return res.status(500).json({ message: 'Database error during permissions creation.' });
        }

        // Menyediakan hasil dari operasi insert
        res.json({ 
          message: 'Permissions processed successfully.', 
          affectedRows: results.affectedRows, // Berapa banyak baris yang diinsert
          ignoredRows: results.warningCount   // Berapa banyak baris yang diabaikan karena duplikat
        });
    });
});

//================================================================================================

// tampilkan pencarian data user permission
app.get('/userps', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT tbl_userpermission.id_permission, tbl_userpermission.user_name, tbl_userpermission.menu_name, tbl_userpermission.is_open, tbl_userpermission.is_addnew, tbl_userpermission.is_update, tbl_userpermission.is_delete, tbl_task.ket_menu FROM tbl_userpermission INNER JOIN tbl_task ON tbl_userpermission.menu_name=tbl_task.menu_name WHERE user_name = ? ORDER BY tbl_task.ket_menu";
  const values = [searchQuery];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// Update data permission
app.put('/userps/:id', (req, res) => {
    const { id } = req.params;
    const { field, value } = req.body;

    const sql = `UPDATE tbl_userpermission SET ${field} = ?, date_modified=NOW() WHERE id_permission = ?`;

    // Jalankan query update
    db.query(sql, [value, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during update.' });
        }

        // Jika tidak ada baris yang diperbarui
        if (result.affectedRows === 0) {
            return res.status(404).json({ message: 'Permission not found or no changes made.' });
        }

        // Jika berhasil
        res.status(200).json({ message: 'Permission updated successfully.', data: result });
    });
});


// Endpoint untuk mengupdate data permission
app.put('/update_permission/:id', (req, res) => {
    const { id } = req.params;
    const { field, value } = req.body;

    console.log(`Received update request: id=${id}, field=${field}, value=${value}`);

    const validFields = ['is_open', 'is_addnew', 'is_update', 'is_delete'];
    if (!validFields.includes(field)) {
        return res.status(400).json({ success: false, message: 'Invalid field' });
    }

    const sql = `UPDATE tbl_userpermission SET ${field} = ?, date_modified = NOW() WHERE id_permission = ?`;

    db.query(sql, [value, id], (err, result) => {
        if (err) {
            console.error("Database error:", err);
            return res.status(500).json({ success: false, message: 'Database error' });
        }

        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Permission not found' });
        }

        res.status(200).json({ success: true, message: 'Permission updated successfully' });
    });
});



//================================================================================================

// tampilkan pencarian data perusahaan
app.get('/perusahaans', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT * FROM tbl_perusahaan WHERE kd_perusahaan LIKE ? OR nm_perusahaan LIKE ?";
  const values = [`%${searchQuery}%`, `%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru perusahaan
app.post('/perusahaans', (req, res) => {

    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const newItem = { kd_perusahaan: req.body.kd_perusahaan, nm_perusahaan: req.body.nm_perusahaan, user_created: req.body.useraktif, date_created:now };
    const sql = 'INSERT INTO tbl_perusahaan SET ?';
    db.query(sql, newItem, (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        //if (err) throw err;
        res.json({ id: result.insertId, ...newItem });
    });

});

// update data perusahaan
app.put('/perusahaans/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { kd_perusahaan, nm_perusahaan, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_perusahaan SET kd_perusahaan=?, nm_perusahaan=?, user_modified=?, date_modified=NOW() WHERE id_perusahaan = ?";
    db.query(sql, [kd_perusahaan, nm_perusahaan, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// hapus data perusahaan
app.delete('/perusahaans/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_perusahaan WHERE id_perusahaan = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


//================================================================================================

// tampilkan pencarian data supplier
app.get('/suppliers', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT * FROM tbl_supplier WHERE kd_supplier LIKE ? OR nm_supplier LIKE ? ORDER BY nm_supplier";
  const values = [`%${searchQuery}%`, `%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru supplier
app.post('/suppliers', (req, res) => {

    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const newItem = {
        nm_supplier: req.body.nm_supplier,
        al_supplier: req.body.al_supplier,
        tl_supplier: req.body.tl_supplier,
        pi_supplier: req.body.pi_supplier,
        user_created: req.body.useraktif,
        date_created: now
    };

    // Query untuk mendapatkan kd_supplier terakhir yang menggunakan format 'SUPxxx'
    const sqlGetLast = "SELECT kd_supplier FROM tbl_supplier WHERE kd_supplier LIKE 'SUP%' ORDER BY kd_supplier DESC LIMIT 1";

    db.query(sqlGetLast, (err, results) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during supplier code generation.' });
        }

        let newKodeSupplier = '';

        if (results.length > 0) {
            // Ambil kode supplier terakhir dan pisahkan angka di belakang 'SUP'
            const lastKode = results[0].kd_supplier;
            const kodeNumber = parseInt(lastKode.replace('SUP', '')) + 1;
            
            // Buat kode baru dengan format 'SUP' dan 3 digit angka, misalnya SUP001
            newKodeSupplier = 'SUP' + kodeNumber.toString().padStart(3, '0');
        } else {
            // Jika belum ada supplier dengan format 'SUPxxx', mulai dari 'SUP001'
            newKodeSupplier = 'SUP001';
        }

        // Masukkan kode baru ke dalam objek newItem
        newItem.kd_supplier = newKodeSupplier;

        const sqlInsert = 'INSERT INTO tbl_supplier SET ?';

        // Lakukan INSERT data baru
        db.query(sqlInsert, newItem, (err, result) => {
            if (err) {
                return res.status(500).json({ message: 'Database error during insert.' });
            }

            // Kirimkan response dengan id baru dan data yang telah di-insert
            res.json({ id: result.insertId, ...newItem });
        });
    });
});

// update data supplier
app.put('/suppliers/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { kd_supplier, nm_supplier, al_supplier, tl_supplier, pi_supplier, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_supplier SET kd_supplier=?, nm_supplier=?, al_supplier=?, tl_supplier=?, pi_supplier=?, user_modified=?, date_modified=NOW() WHERE id_supplier = ?";
    db.query(sql, [kd_supplier, nm_supplier, al_supplier, tl_supplier, pi_supplier, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// hapus data supplier
app.delete('/suppliers/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_supplier WHERE id_supplier = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});

//================================================================================================

// tampilkan pencarian data kategori
app.get('/kategoris', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT * FROM tbl_kategori WHERE kd_kategori LIKE ? OR nm_kategori LIKE ?";
  const values = [`%${searchQuery}%`, `%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru kategori
app.post('/kategoris', (req, res) => {

    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const newItem = { kd_kategori: req.body.kd_kategori, nm_kategori: req.body.nm_kategori, user_created: req.body.useraktif, date_created:now };
    const sql = 'INSERT INTO tbl_kategori SET ?';
    db.query(sql, newItem, (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        //if (err) throw err;
        res.json({ id: result.insertId, ...newItem });
    });

});

// update data kategori
app.put('/kategoris/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { kd_kategori, nm_kategori, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_kategori SET kd_kategori=?, nm_kategori=?, user_modified=?, date_modified=NOW() WHERE id_kategori = ?";
    db.query(sql, [kd_kategori, nm_kategori, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// hapus data kategori
app.delete('/kategoris/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_kategori WHERE id_kategori = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


//================================================================================================

// tampilkan pencarian data satuan
app.get('/satuans', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT * FROM tbl_satuan WHERE kd_satuan LIKE ? OR nm_satuan LIKE ?";
  const values = [`%${searchQuery}%`, `%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru satuan
app.post('/satuans', (req, res) => {

    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const newItem = { kd_satuan: req.body.kd_satuan, nm_satuan: req.body.nm_satuan, user_created: req.body.useraktif, date_created:now };
    const sql = 'INSERT INTO tbl_satuan SET ?';
    db.query(sql, newItem, (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        //if (err) throw err;
        res.json({ id: result.insertId, ...newItem });
    });

});

// update data satuan
app.put('/satuans/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { kd_satuan, nm_satuan, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_satuan SET kd_satuan=?, nm_satuan=?, user_modified=?, date_modified=NOW() WHERE id_satuan = ?";
    db.query(sql, [kd_satuan, nm_satuan, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// hapus data satuan
app.delete('/satuans/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_satuan WHERE id_satuan = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


//================================================================================================

// tampilkan pencarian data metode bayar
app.get('/metodepays', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT * FROM tbl_metode_bayar WHERE kd_metode_bayar LIKE ? OR nm_metode_bayar LIKE ?";
  const values = [`%${searchQuery}%`, `%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru metode_bayar
app.post('/metodepays', (req, res) => {

    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const newItem = { kd_metode_bayar: req.body.kd_metode_bayar, nm_metode_bayar: req.body.nm_metode_bayar, user_created: req.body.useraktif, date_created:now };
    const sql = 'INSERT INTO tbl_metode_bayar SET ?';
    db.query(sql, newItem, (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        //if (err) throw err;
        res.json({ id: result.insertId, ...newItem });
    });

});

// update data metode_bayar
app.put('/metodepays/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { kd_metode_bayar, nm_metode_bayar, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_metode_bayar SET kd_metode_bayar=?, nm_metode_bayar=?, user_modified=?, date_modified=NOW() WHERE id_metode_bayar = ?";
    db.query(sql, [kd_metode_bayar, nm_metode_bayar, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// hapus data metode_bayar
app.delete('/metodepays/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_metode_bayar WHERE id_metode_bayar = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


//================================================================================================

// tampilkan pencarian data barang
app.get('/barangs', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT tbl_barang.*, tbl_supplier.nm_supplier, tbl_kategori.nm_kategori FROM tbl_barang INNER JOIN tbl_supplier ON tbl_barang.kd_supplier=tbl_supplier.kd_supplier INNER JOIN tbl_kategori ON tbl_barang.kd_kategori=tbl_kategori.kd_kategori WHERE kd_barang LIKE ? OR nm_barang LIKE ?";
  const values = [`%${searchQuery}%`, `%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru barang
//app.post('/barangs', upload.single('foto_barang'), (req, res) => {
app.post('/barangs', (req, res) => {

    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const newItem = {
        nm_barang: req.body.nm_barang,
        kd_kategori: req.body.kd_kategori,
        kd_supplier: req.body.kd_supplier,
        kd_satuan_besar: req.body.kd_satuan_besar,
        qty_besar: req.body.qty_besar,
        kd_satuan_kecil: req.body.kd_satuan_kecil,
        qty_kecil: req.body.qty_kecil,
        is_stok: req.body.is_stok,
        foto_barang: req.body.foto_barang,
        //foto_barang: req.file ? req.file.filename : null, //req.body.foto_barang,
        user_created: req.body.useraktif,
        date_created: now
    };

    // Query untuk mendapatkan kd_barang terakhir yang menggunakan format 'BRGxxxx'
    const sqlGetLast = "SELECT kd_barang FROM tbl_barang WHERE kd_barang LIKE 'BRG%' ORDER BY kd_barang DESC LIMIT 1";

    db.query(sqlGetLast, (err, results) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during barang code generation.' });
        }

        let newKodeBarang = '';

        if (results.length > 0) {
            // Ambil kode barang terakhir dan pisahkan angka di belakang 'BRG'
            const lastKode = results[0].kd_barang;
            const kodeNumber = parseInt(lastKode.replace('BRG', '')) + 1;
            
            // Buat kode baru dengan format 'BRG' dan 4 digit angka, misalnya BRG0001
            newKodeBarang = 'BRG' + kodeNumber.toString().padStart(4, '0');
        } else {
            // Jika belum ada supplier dengan format 'SUPxxx', mulai dari 'SUP001'
            newKodeBarang = 'BRG0001';
        }

        // Masukkan kode baru ke dalam objek newItem
        newItem.kd_barang = newKodeBarang;

        const sqlInsert = 'INSERT INTO tbl_barang SET ?';

        // Lakukan INSERT data baru
        db.query(sqlInsert, newItem, (err, result) => {
            if (err) {
                return res.status(500).json({ message: 'Database error during insert.' });
            }

            // Kirimkan response dengan id baru dan data yang telah di-insert
            res.json({ id: result.insertId, ...newItem });
        });
    });
});

// update data barang
app.put('/barangs/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { kd_barang, nm_barang, kd_kategori, kd_supplier, kd_satuan_besar, qty_besar, kd_satuan_kecil, qty_kecil, is_stok, foto_barang, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_barang SET kd_barang=?, nm_barang=?, kd_kategori=?, kd_supplier=?, kd_satuan_besar=?, qty_besar=?, kd_satuan_kecil=?, qty_kecil=?, is_stok=?, foto_barang=?, user_modified=?, date_modified=NOW() WHERE id_barang = ?";
    db.query(sql, [kd_barang, nm_barang, kd_kategori, kd_supplier, kd_satuan_besar, qty_besar, kd_satuan_kecil, qty_kecil, is_stok, foto_barang, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// hapus data barang
app.delete('/barangs/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_barang WHERE id_barang = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


//================================================================================================

// tampilkan pencarian data beli
app.get('/belis', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT tbl_beli_main.*, tbl_supplier.nm_supplier,  tbl_supplier.tl_supplier, IFNULL(tbl_bayar_detail.nilai_bayar,0) AS tot_bayar, tbl_beli_main.tot_beli-IFNULL(tbl_bayar_detail.nilai_bayar,0) AS tot_hutang FROM tbl_beli_main LEFT JOIN tbl_supplier ON tbl_beli_main.kd_supplier=tbl_supplier.kd_supplier LEFT JOIN tbl_bayar_detail ON tbl_beli_main.no_beli=tbl_bayar_detail.no_beli WHERE tbl_beli_main.no_beli LIKE ? OR tbl_beli_main.tgl_beli LIKE ? OR tbl_supplier.nm_supplier LIKE ? ORDER BY tbl_beli_main.no_beli DESC";
  const values = [`%${searchQuery}%`, `%${searchQuery}%`, `%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});



// tambah data baru beli
app.post('/belis', (req, res) => {
    const now = new Date(); // Timestamp sekarang
    const tgl_beli = new Date(req.body.tgl_beli); // Konversi tgl_beli menjadi objek Date

    // Mendapatkan dua digit terakhir tahun dan bulan dengan format 'YYMM'
    const yearMonth = tgl_beli.getFullYear().toString().slice(2) + (tgl_beli.getMonth() + 1).toString().padStart(2, '0');

    const newItem = {
        no_beli: '',
        tgl_beli: req.body.tgl_beli,
        kd_supplier: req.body.kd_supplier,
        ket_beli: req.body.ket_beli,
        sub_beli: req.body.sub_beli,
        pot_beli: req.body.pot_beli,
        ppn_beli: req.body.ppn_beli,
        tot_beli: req.body.tot_beli,
        foto_bon: req.body.foto_bon,
        user_created: req.body.useraktif,
        date_created: now
    };

    // Query untuk mendapatkan no_beli terakhir yang menggunakan format 'PB[YY][MM]xxxx'
    const sqlGetLast = `SELECT no_beli FROM tbl_beli_main WHERE DATE_FORMAT(tgl_beli, '%y%m') = ? ORDER BY no_beli DESC LIMIT 1`;

    db.query(sqlGetLast, [yearMonth], (err, results) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during no_beli generation.' });
        }

        let newNoBeli = '';

        if (results.length > 0) {
            // Ambil no beli terakhir dan pisahkan angka di belakang 'PB[YY][MM]'
            const lastNo = results[0].no_beli;
            const kodeNumber = parseInt(lastNo.replace('PB' + yearMonth, '')) + 1;

            // Buat kode baru dengan format 'PB' dan 4 digit angka, misalnya PB23090001
            newNoBeli = 'PB' + yearMonth + kodeNumber.toString().padStart(4, '0');
        } else {
            // Jika belum ada no_beli dengan format 'PBxxx', mulai dari 'PB0001'
            newNoBeli = 'PB' + yearMonth + '0001';
        }

        // Masukkan kode baru ke dalam objek newItem
        newItem.no_beli = newNoBeli;

        const sqlInsert = 'INSERT INTO tbl_beli_main SET ?';

        // Lakukan INSERT data baru
        db.query(sqlInsert, newItem, (err, result) => {
            if (err) {
                return res.status(500).json({ message: 'Database error during insert.' });
            }

            // Kirimkan response dengan id baru dan data yang telah di-insert
            res.json({ id: result.insertId, ...newItem });
        });
    });
});



// update data beli
app.put('/belis/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { no_beli, tgl_beli, kd_supplier, ket_beli, sub_beli, pot_beli, ppn_beli, tot_beli, foto_bon, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_beli_main SET no_beli=?, tgl_beli=?, kd_supplier=?, ket_beli=?, sub_beli=?, pot_beli=?, ppn_beli=?, tot_beli=?, foto_bon=?, user_modified=?, date_modified=NOW() WHERE id_beli = ?";
    db.query(sql, [no_beli, tgl_beli, kd_supplier, ket_beli, parseFloat(sub_beli.replace(/,/g, '')) || 0, parseFloat(pot_beli.replace(/,/g, '')) || 0, parseFloat(ppn_beli.replace(/,/g, '')) || 0, parseFloat(tot_beli.replace(/,/g, '')) || 0, foto_bon, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// hapus data beli
app.delete('/belis/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_beli_main WHERE id_beli = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


//================================================================================================

// tampilkan pencarian data beli -> detail barang
app.get('/belidets', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT tbl_beli_detail.*, tbl_barang.nm_barang, tbl_perusahaan.nm_perusahaan FROM tbl_beli_detail LEFT JOIN tbl_barang ON tbl_beli_detail.kd_barang=tbl_barang.kd_barang LEFT JOIN tbl_perusahaan ON tbl_beli_detail.kd_perusahaan=tbl_perusahaan.kd_perusahaan WHERE no_beli LIKE ? ORDER BY tbl_beli_detail.no_beli, tbl_beli_detail.kd_barang, tbl_beli_detail.kd_perusahaan ASC";
  const values = [`%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru beli -> detail barang
app.post('/belidets', (req, res) => {
    const { no_beli, kd_barang, qty_beli, harga_beli, total_beli, kd_perusahaan, useraktif } = req.body;

    // Query untuk insert ke tbl_beli_detail
    const sqlInsertDetail = `
        INSERT INTO tbl_beli_detail (no_beli, kd_barang, qty_beli, harga_beli, total_beli, kd_perusahaan, user_created, date_created)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    `;

    // Lakukan insert ke tbl_beli_detail
    db.query(sqlInsertDetail, [
        no_beli,
        kd_barang,
        parseFloat(qty_beli.replace(/,/g, '')) || 0,
        parseFloat(harga_beli.replace(/,/g, '')) || 0,
        parseFloat(total_beli.replace(/,/g, '')) || 0,
        kd_perusahaan,
        useraktif
    ], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during insert process in tbl_beli_detail.' });
        }

        // Setelah berhasil insert ke tbl_beli_detail, ambil summary total_beli dari tbl_beli_detail
        const sqlSumTotalBeli = `
            SELECT SUM(total_beli) AS total_beli_sum
            FROM tbl_beli_detail
            WHERE no_beli = ?
        `;

        db.query(sqlSumTotalBeli, [no_beli], (err, resultSum) => {
            if (err) {
                return res.status(500).json({ message: 'Error calculating total_beli in tbl_beli_detail.' });
            }

            // Ambil nilai total_beli_sum dari hasil query
            const sub_beli = resultSum[0].total_beli_sum || 0;

            // Update sub_beli di tbl_beli_main
            const sqlUpdateMain = `
                UPDATE tbl_beli_main 
                SET sub_beli = ?, tot_beli = sub_beli - pot_beli + ppn_beli, user_modified = ?, date_modified = NOW()
                WHERE no_beli = ?
            `;

            db.query(sqlUpdateMain, [sub_beli, useraktif, no_beli], (err, resultMain) => {
                if (err) {
                    return res.status(500).json({ message: 'Database error during update process in tbl_beli_main.' });
                }

                res.json({ message: 'Data inserted and total_beli updated successfully', resultMain });
            });
        });
    });
});


// update data beli -> detail barang
app.put('/belidets/:id', (req, res) => {
    const { id } = req.params; // id beli
    const { no_beli, kd_barang, qty_beli, harga_beli, total_beli, kd_perusahaan, useraktif } = req.body;

    // Query untuk update tbl_beli_detail
    const sqlUpdateDetail = `
        UPDATE tbl_beli_detail 
        SET no_beli = ?, kd_barang = ?, qty_beli = ?, harga_beli = ?, total_beli = ?, kd_perusahaan=?, user_modified = ?, date_modified = NOW()
        WHERE id_beli_detail = ?
    `;

    // Lakukan update tbl_beli_detail
    db.query(sqlUpdateDetail, [
        no_beli,
        kd_barang,
        parseFloat(qty_beli.replace(/,/g, '')) || 0,
        parseFloat(harga_beli.replace(/,/g, '')) || 0,
        parseFloat(total_beli.replace(/,/g, '')) || 0,
        kd_perusahaan, 
        useraktif,
        id
    ], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during update process in tbl_beli_detail.' });
        }

        // Setelah berhasil update tbl_beli_detail, ambil summary total_beli dari tbl_beli_detail
        const sqlSumTotalBeli = `
            SELECT SUM(total_beli) AS total_beli_sum
            FROM tbl_beli_detail
            WHERE no_beli = ?
        `;

        db.query(sqlSumTotalBeli, [no_beli], (err, resultSum) => {
            if (err) {
                return res.status(500).json({ message: 'Error calculating total_beli in tbl_beli_detail.' });
            }

            // Ambil nilai total_beli_sum dari hasil query
            const sub_beli = resultSum[0].total_beli_sum || 0;

            // Update sub_beli di tbl_beli_main
            const sqlUpdateMain = `
                UPDATE tbl_beli_main 
                SET sub_beli = ?, tot_beli = sub_beli - pot_beli + ppn_beli, user_modified = ?, date_modified = NOW()
                WHERE no_beli = ?
            `;

            db.query(sqlUpdateMain, [sub_beli, useraktif, no_beli], (err, resultMain) => {
                if (err) {
                    return res.status(500).json({ message: 'Database error during update process in tbl_beli_main.' });
                }

                res.json({ message: 'Data updated successfully', resultMain });
            });
        });
    });
});


// hapus data beli -> detail barang
app.delete('/belidets/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_beli_detail WHERE id_beli_detail = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


//================================================================================================

// tampilkan pencarian data bayar
app.get('/bayars', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT tbl_bayar_main.*, tbl_supplier.nm_supplier,  tbl_supplier.tl_supplier FROM tbl_bayar_main LEFT JOIN tbl_supplier ON tbl_bayar_main.kd_supplier=tbl_supplier.kd_supplier WHERE tbl_bayar_main.no_bayar LIKE ? OR tbl_bayar_main.tgl_bayar LIKE ? OR tbl_supplier.nm_supplier LIKE ? ORDER BY tbl_bayar_main.no_bayar DESC LIMIT 100";
  const values = [`%${searchQuery}%`, `%${searchQuery}%`, `%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru bayar
app.post('/bayars', (req, res) => {
    const now = new Date(); // Timestamp sekarang
    const tgl_bayar = new Date(req.body.tgl_bayar); // Konversi tgl_bayar menjadi objek Date

    // Mendapatkan dua digit terakhir tahun dan bulan dengan format 'YYMM'
    const yearMonth = tgl_bayar.getFullYear().toString().slice(2) + (tgl_bayar.getMonth() + 1).toString().padStart(2, '0');

    const newItem = {
        no_bayar: '',
        tgl_bayar: req.body.tgl_bayar,
        kd_supplier: req.body.kd_supplier,
        ket_bayar: req.body.ket_bayar,
        kd_metode_bayar: req.body.kd_metode_bayar,
        total_bayar: req.body.total_bayar,
        foto_bayar: req.body.foto_bayar,
        user_created: req.body.useraktif,
        date_created: now
    };

    // Query untuk mendapatkan no_bayar terakhir yang menggunakan format 'PB[YY][MM]xxxx'
    const sqlGetLast = `SELECT no_bayar FROM tbl_bayar_main WHERE DATE_FORMAT(tgl_bayar, '%y%m') = ? ORDER BY no_bayar DESC LIMIT 1`;

    db.query(sqlGetLast, [yearMonth], (err, results) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during no_bayar generation.' });
        }

        let newNoBayar = '';

        if (results.length > 0) {
            // Ambil no beli terakhir dan pisahkan angka di belakang 'PB[YY][MM]'
            const lastNo = results[0].no_bayar;
            const kodeNumber = parseInt(lastNo.replace('BY' + yearMonth, '')) + 1;

            // Buat kode baru dengan format 'PB' dan 4 digit angka, misalnya PB23090001
            newNoBayar = 'BY' + yearMonth + kodeNumber.toString().padStart(4, '0');
        } else {
            // Jika belum ada no_beli dengan format 'PBxxx', mulai dari 'PB0001'
            newNoBayar = 'BY' + yearMonth + '0001';
        }

        // Masukkan kode baru ke dalam objek newItem
        newItem.no_bayar = newNoBayar;

        const sqlInsert = 'INSERT IGNORE INTO tbl_bayar_main SET ?';

        // Lakukan INSERT data baru
        db.query(sqlInsert, newItem, (err, result) => {
            if (err) {
                return res.status(500).json({ message: 'Database error during insert.' });
            }

            // Kirimkan response dengan id baru dan data yang telah di-insert
            res.json({ id: result.insertId, ...newItem });
        });
    });
});



// update data bayar
app.put('/bayars/:id', (req, res) => {
    const now = new Date(); // Ini menghasilkan timestamp sekarang
    const { id } = req.params;
    const { no_bayar, tgl_bayar, kd_supplier, ket_bayar, kd_metode_bayar, total_bayar, foto_bayar, user_modified, date_modified, useraktif } = req.body;
    const sql = "UPDATE tbl_bayar_main SET no_bayar=?, tgl_bayar=?, kd_supplier=?, ket_bayar=?, kd_metode_bayar=?, total_bayar=?, foto_bayar=?, user_modified=?, date_modified=NOW() WHERE id_bayar_main = ?";
    db.query(sql, [no_bayar, tgl_bayar, kd_supplier, ket_bayar, kd_metode_bayar, parseFloat(total_bayar.replace(/,/g, '')) || 0, foto_bayar, useraktif, id], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during duplicate check.' });
        }

        // Jika data sudah ada, kirimkan pesan duplikasi
        if (result.length > 0) {
            return res.status(409).json({ message: 'Data already exists. Please provide unique data.' });
        }

        res.json(result);
    });
});

// hapus data bayar
app.delete('/bayars/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_bayar_main WHERE id_bayar_main = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});


//================================================================================================

// tampilkan pencarian data bayar -> beli main
app.get('/bayardets', (req, res) => {

  const searchQuery = req.query.q;
  const query = "SELECT tbl_bayar_detail.*, tbl_beli_main.tot_beli AS total_beli, tbl_bayar_detail.nilai_bayar-tbl_beli_main.tot_beli AS sisa_hutang FROM tbl_bayar_detail LEFT JOIN tbl_beli_main ON tbl_bayar_detail.no_beli=tbl_beli_main.no_beli WHERE no_bayar LIKE ? ORDER BY tbl_bayar_detail.no_bayar, tbl_bayar_detail.no_beli ASC";
  const values = [`%${searchQuery}%`];

  db.query(query, values, (err, results) => {
    if (err) throw err;
    res.json(results);
  });
});


// tambah data baru bayar -> beli main
app.post('/bayardets', (req, res) => {
    const { no_bayar, no_beli, nilai_bayar, useraktif } = req.body;

    // Query untuk insert ke tbl_bayar_detail
    const sqlInsertDetail = `
        INSERT INTO tbl_bayar_detail (no_bayar, no_beli, nilai_bayar, user_modified, date_modified)
        VALUES (?, ?, ?, ?, NOW())
    `;

    // Lakukan insert ke tbl_bayar_detail
    db.query(sqlInsertDetail, [
        no_bayar,
        no_beli,
        parseFloat(nilai_bayar.replace(/,/g, '')) || 0,
        useraktif
    ], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during insert process in tbl_beli_detail.' });
        }

        // Setelah berhasil insert ke tbl_bayar_detail, ambil summary nilai_bayar dari tbl_bayar_detail
        const sqlSumTotalBayar = `
            SELECT SUM(nilai_bayar) AS total_bayar_sum
            FROM tbl_bayar_detail
            WHERE no_bayar = ?
        `;

        db.query(sqlSumTotalBayar, [no_bayar], (err, resultSum) => {
            if (err) {
                return res.status(500).json({ message: 'Error calculating total_beli in tbl_beli_detail.' });
            }

            // Ambil nilai total_beli_sum dari hasil query
            const total_bayar = resultSum[0].total_bayar_sum || 0;

            // Update sub_beli di tbl_beli_main
            const sqlUpdateMain = `
                UPDATE tbl_bayar_main 
                SET total_bayar = ?, user_modified = ?, date_modified = NOW()
                WHERE no_bayar = ?
            `;

            db.query(sqlUpdateMain, [total_bayar, useraktif, no_bayar], (err, resultMain) => {
                if (err) {
                    return res.status(500).json({ message: 'Database error during update process in tbl_bayar_main.' });
                }

                res.json({ message: 'Data inserted and total_beli updated successfully', resultMain });
            });
        });
    });
});





// update data bayar -> beli main
app.put('/bayardets/:id', (req, res) => {
    const { id } = req.params; // id beli
    const { no_bayar, no_beli, nilai_bayar, useraktif } = req.body;

    // Query untuk update tbl_bayar_detail
    const sqlUpdateDetail = `
        UPDATE tbl_bayar_detail 
        SET no_bayar=?, no_beli = ?, nilai_bayar = ?, user_modified = ?, date_modified = NOW()
        WHERE id_bayar_detail = ?
    `;

    // Lakukan update tbl_bayar_detail
    db.query(sqlUpdateDetail, [
        no_bayar,
        no_beli,
        parseFloat(nilai_bayar.replace(/,/g, '')) || 0,
        useraktif,
        id
    ], (err, result) => {
        if (err) {
            return res.status(500).json({ message: 'Database error during update process in tbl_bayar_detail.' });
        }

        // Setelah berhasil update tbl_bayar_detail, ambil summary total_beli dari tbl_beli_detail
        const sqlSumTotalBayar = `
            SELECT SUM(nilai_bayar) AS total_bayar_sum
            FROM tbl_bayar_detail
            WHERE no_bayar = ?
        `;

        db.query(sqlSumTotalBayar, [no_bayar], (err, resultSum) => {
            if (err) {
                return res.status(500).json({ message: 'Error calculating total_beli in tbl_bayar_detail.' });
            }

            // Ambil nilai total_bayar_sum dari hasil query
            const sum_bayar = resultSum[0].total_bayar_sum || 0;

            // Update total_bayar di tbl_bayar_main
            const sqlUpdateMain = `
                UPDATE tbl_bayar_main 
                SET total_bayar = ?, user_modified = ?, date_modified = NOW()
                WHERE no_bayar = ?
            `;

            db.query(sqlUpdateMain, [sum_bayar, useraktif, no_bayar], (err, resultMain) => {
                if (err) {
                    return res.status(500).json({ message: 'Database error during update process in tbl_beli_main.' });
                }

                res.json({ message: 'Data updated successfully', resultMain });
            });
        });
    });
});


// hapus data bayar -> beli main
app.delete('/bayardets/:id', (req, res) => {
    const { id } = req.params;
    const sql = 'DELETE FROM tbl_bayar_detail WHERE id_bayar_detail = ?';
    db.query(sql, id, (err, result) => {
        if (err) throw err;
        res.json({ message: 'Item deleted' });
    });
});

//================================================================================================

app.get('/belipages', (req, res) => {
  const searchQuery = req.query.q || '';
  const limit = parseInt(req.query.length);  // Jumlah data per halaman
  const offset = parseInt(req.query.start);  // Data mulai dari
  const draw = req.query.draw;  // Penghitung permintaan

  const query = 'SELECT tbl_beli_main.*, tbl_supplier.nm_supplier,  tbl_supplier.tl_supplier, IFNULL(tbl_bayar_detail.nilai_bayar,0) AS tot_bayar, tbl_beli_main.tot_beli-IFNULL(tbl_bayar_detail.nilai_bayar,0) AS tot_hutang FROM tbl_beli_main LEFT JOIN tbl_supplier ON tbl_beli_main.kd_supplier=tbl_supplier.kd_supplier LEFT JOIN tbl_bayar_detail ON tbl_beli_main.no_beli=tbl_bayar_detail.no_beli WHERE tbl_beli_main.no_beli LIKE ? ORDER BY tbl_beli_main.no_beli DESC LIMIT ? OFFSET ?';
  const values = [`%${searchQuery}%`, limit, offset];

  db.query(query, values, (err, results) => {
    if (err) throw err;

    // Hitung total data tanpa filter
    db.query("SELECT COUNT(*) AS total FROM tbl_beli_main", (err, countResults) => {
      const totalRecords = countResults[0].total;

      // Hitung total data yang cocok dengan filter
      db.query("SELECT COUNT(*) AS filtered FROM tbl_beli_main WHERE no_beli LIKE ?", [`%${searchQuery}%`], (err, filteredResults) => {
        const totalFilteredRecords = filteredResults[0].filtered;

        // Kembalikan data dalam format yang sesuai untuk DataTables
        res.json({
          draw: draw,
          recordsTotal: totalRecords,
          recordsFiltered: totalFilteredRecords,
          data: results
        });
      });
    });
  });
});



app.listen(port, () => {
    console.log(`Server running on http://localhost:${port}`);
});
