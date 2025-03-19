/// <reference types="cypress" />

// Welcome to Cypress!
//
// This spec file contains a variety of sample tests
// for a todo list app that are designed to demonstrate
// the power of writing tests in Cypress.
//
// To learn more about how Cypress works and
// what makes it such an awesome testing tool,
// please read our getting started guide:
// https://on.cypress.io/introduction-to-cypress
describe('Dashboard Menu User Test', () => {
      beforeEach(() => {
        cy.session('login', () => {
            cy.login('1978.MTK.1222', '123456');// Ganti dengan kredensial login Anda
            cy.wait(1000);
            cy.getCookies().should('exist'); // Pastikan cookie tersimpan
          });
          cy.visit('http://localhost:8000/home');
          cy.get('#sidebar').should('exist').and('be.visible');
          cy.contains('Master').click();
          cy.contains('Users').click();
          cy.wait(1000);
      });

      it('Negatif Case: Different input Password dan Confirm Password ', () => {
        cy.contains('TAMBAH').click();
        // Enter valid credentials
        cy.get('input[name="nik"]').type('1978.MTK.1225');
        cy.get('input[name="nama"]').type('AKUN TIGA');

        // Klik pada dropdown untuk membuka daftar opsi JABATAN
        cy.get('select#jabatan.form-control option:selected').should('have.text', 'PILIH');
        // Pastikan dropdown terlihat
        cy.get('select#jabatan.form-control').scrollIntoView().should('be.visible');
        // Pilih opsi dari dropdown dengan memaksa jika diperlukan
        cy.get('select#jabatan.form-control').select('ADMIN LOGISTIK', { force: true });

        // Klik pada dropdown untuk membuka daftar opsi ROLES
        cy.get('select#roles.form-control option:selected').should('have.text', 'PILIH');
        // Pastikan dropdown terlihat
        cy.get('select#roles.form-control').scrollIntoView().should('be.visible');
        // Pilih opsi dari dropdown dengan memaksa jika diperlukan
        cy.get('select#roles.form-control').select('ADMIN', { force: true });

        // Validasi Region & Unit
        cy.validateRegionUnit('PT MITRA MAHKOTA BUANA', '[HO MMB] HEAD OFFICE PT MMB');

        // Nama file yang akan diunggah
        const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

        // Pilih elemen input file berdasarkan class .form-control
        cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
        .attachFile(fileName); // Mengunggah file dari folder "fixtures"

        // **Password & Confirm Password Validation**
        const password = '56789';
        const confirmPassword = '12345';

        cy.get('input#password').type(password);
        cy.get('input#confirm_password').type(confirmPassword);

        cy.get('span#message') // Pastikan error "Password tidak sama" muncul
            .should('be.visible')
            .and('contain', 'Password tidak sama');

        // Jika password dan confirm password tidak cocok, gagal menyimpan
        if (password !== confirmPassword) {
            cy.log('Error: Password dan Confirm Password tidak cocok!');

            // Klik SIMPAN
            cy.contains('SIMPAN').click();

            // **Validasi SweetAlert Gagal**
            cy.get('.swal2-popup')
                .should('be.visible')
                .within(() => {
                    cy.get('.swal2-html-container').should('contain', 'Password dan Konfirmasi Password tidak sesuai!');
                });

            // **Tambahkan error jika SweetAlert tidak muncul**
            cy.get('body').then(($body) => {
                if (!$body.find('.swal2-popup').length) {
                    throw new Error('Error: SweetAlert tidak muncul saat password tidak cocok.');
                }
            });
        }
    });

    it('Negatif Case: Empty field NIK', () => {
        cy.contains('TAMBAH').click();
        // Enter valid credentials
        cy.get('input[name="nik"]').should('have.value', '');
        cy.get('input[name="nama"]').type('Nur Widianti');

        // Klik pada dropdown untuk membuka daftar opsi JABATAN
        cy.get('select#jabatan.form-control option:selected').should('have.text', 'PILIH');
        // Pastikan dropdown terlihat
        cy.get('select#jabatan.form-control').scrollIntoView().should('be.visible');
        // Pilih opsi dari dropdown dengan memaksa jika diperlukan
        cy.get('select#jabatan.form-control').select('ADMIN LOGISTIK', { force: true });

        // Klik pada dropdown untuk membuka daftar opsi ROLES
        cy.get('select#roles.form-control option:selected').should('have.text', 'PILIH');
        // Pastikan dropdown terlihat
        cy.get('select#roles.form-control').scrollIntoView().should('be.visible');
        // Pilih opsi dari dropdown dengan memaksa jika diperlukan
        cy.get('select#roles.form-control').select('ADMIN', { force: true });

        // Validasi Region & Unit
        cy.validateRegionUnit('PT LAWU ABADI NUSA', '[SKH] SUKOHARJO');

        // Nama file yang akan diunggah
        const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

        // Pilih elemen input file berdasarkan class .form-control
        cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
        .attachFile(fileName); // Mengunggah file dari folder "fixtures"

        cy.get('input#password').type('Nurwidianti35_@');
        cy.get('input#confirm_password').type('Nurwidianti35_@');
        cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
        .should('be.visible')
        .and('contain', 'Password sama');
        cy.wait(500);
        cy.contains('SIMPAN').click();
        // **Pastikan form tidak terkirim karena NIK kosong**
        cy.get('input[name="nik"]').then(($input) => {
            const inputElement = $input[0]; // Ambil elemen DOM mentah
            expect(inputElement.checkValidity()).to.be.false; // Pastikan validasi gagal
            expect(inputElement.validationMessage).to.eq('Please fill out this field.');
        });

        // **Tambahan error handling jika form tetap terkirim**
        cy.get('body').then(($body) => {
            if ($body.find('.swal2-popup, .swal2-toast').length) {
                cy.log('ERROR: Form tetap terkirim meskipun NIK kosong!');
                throw new Error('Form tidak boleh terkirim jika NIK kosong.');
            }

      });
    });

    it('Negatif Case: Empty field Nama', () => {
        cy.contains('TAMBAH').click();
        // Enter valid credentials
        cy.get('input[name="nik"]').type('1978.MTK.1223');
        cy.get('input[name="nama"]').should('have.value', '');

        // Klik pada dropdown untuk membuka daftar opsi JABATAN
        cy.get('select#jabatan.form-control option:selected').should('have.text', 'PILIH');
        // Pastikan dropdown terlihat
        cy.get('select#jabatan.form-control').scrollIntoView().should('be.visible');
        // Pilih opsi dari dropdown dengan memaksa jika diperlukan
        cy.get('select#jabatan.form-control').select('ADMIN LOGISTIK', { force: true });

        // Klik pada dropdown untuk membuka daftar opsi ROLES
        cy.get('select#roles.form-control option:selected').should('have.text', 'PILIH');
        // Pastikan dropdown terlihat
        cy.get('select#roles.form-control').scrollIntoView().should('be.visible');
        // Pilih opsi dari dropdown dengan memaksa jika diperlukan
        cy.get('select#roles.form-control').select('ADMIN', { force: true });

        // Validasi Region & Unit
        cy.validateRegionUnit('PT LAWU ABADI NUSA', '[SKH] SUKOHARJO');

        // Nama file yang akan diunggah
        const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

        // Pilih elemen input file berdasarkan class .form-control
        cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
        .attachFile(fileName); // Mengunggah file dari folder "fixtures"

        cy.get('input#password').type('Nurwidianti35_@');
        cy.get('input#confirm_password').type('Nurwidianti35_@');
        cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
        .should('be.visible')
        .and('contain', 'Password sama');
        cy.wait(500);
        cy.contains('SIMPAN').click();
         // **Pastikan form tidak terkirim karena nama kosong**
         cy.get('input[name="nama"]').then(($input) => {
            const inputElement = $input[0]; // Ambil elemen DOM mentah
            expect(inputElement.checkValidity()).to.be.false; // Pastikan validasi gagal
            expect(inputElement.validationMessage).to.eq('Please fill out this field.');
        });

        // **Tambahan error handling jika form tetap terkirim**
        cy.get('body').then(($body) => {
            if ($body.find('.swal2-popup, .swal2-toast').length) {
                cy.log('ERROR: Form tetap terkirim meskipun Nama kosong!');
                throw new Error('Form tidak boleh terkirim jika Nama kosong.');
            }
      });
    });

    // it('Negatif Case: Empty field Jabatan', () => {
    //     cy.contains('TAMBAH').click();
    //     // Enter valid credentials
    //     cy.get('input[name="nik"]').type('1978.MTK.1223');
    //     cy.get('input[name="nama"]').type('Icha')

    //     // Jangan pilih apa pun di dropdown JABATAN
    //     cy.get('select#jabatan.form-control').should('have.value', 'PILIH'); // Asumsikan default value kosong

    //     // Klik pada dropdown untuk membuka daftar opsi ROLES
    //     cy.get('select#roles.form-control option:selected').should('have.text', 'PILIH');
    //     // Pastikan dropdown terlihat
    //     cy.get('select#roles.form-control').scrollIntoView().should('be.visible');
    //     // Pilih opsi dari dropdown dengan memaksa jika diperlukan
    //     cy.get('select#roles.form-control').select('ADMIN', { force: true });

    //     // Validasi Region & Unit
    //     cy.validateRegionUnit('PT LAJU SATWA WISESA', ['[HO LSW] HEAD OFFICE PT LSW', '[KDR] KEDIRI', '[JBG] JOMBANG', '[TLA] TULUNGAGUNG']);

    //     // Klik pada dropdown untuk membuka daftar opsi UNIT
    //     cy.get('select#unit.form-control option:selected').should('have.text', 'PILIH');
    //     // Pastikan dropdown terlihat
    //     cy.get('select#unit.form-control').scrollIntoView().should('be.visible');
    //     // Pilih opsi dari dropdown dengan memaksa jika diperlukan
    //     cy.get('select#unit.form-control').select('[KDR] KEDIRI', { force: true });

    //     // Nama file yang akan diunggah
    //     const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

    //     // Pilih elemen input file berdasarkan class .form-control
    //     cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
    //     .attachFile(fileName); // Mengunggah file dari folder "fixtures"

    //     cy.get('input#password').type('Nurwidianti35_@');
    //     cy.get('input#confirm_password').type('Nurwidianti35_@');
    //     cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
    //     .should('be.visible')
    //     .and('contain', 'Password sama');
    //     cy.wait(500);
    //     cy.contains('SIMPAN').click();

    //     // Verifikasi bahwa form tidak terkirim karena validasi required
    //     cy.get('select#jabatan.form-control')
    //     .then(($dropdown) => {
    //     const dropdownElement = $dropdown[0]; // Ambil elemen DOM mentah
    //     expect(dropdownElement.checkValidity()).to.be.false; // Pastikan validasi gagal
    //     expect(dropdownElement.validationMessage).to.eq('Please select an item.'); // Sesuaikan pesan jika custom
    //   });
    // });


//     it('Negatif Case: Empty field Roles', () => {
//         cy.contains('TAMBAH').click();
//         // Enter valid credentials
//         cy.get('input[name="nik"]').type('1978.MTK.1223');
//         cy.get('input[name="nama"]').type('Icha')

//         // Klik pada dropdown untuk membuka daftar opsi JABATAN
//         cy.get('select#jabatan.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#jabatan.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#jabatan.form-control').select('DIREKTUR UTAMA', { force: true });

//         // Jangan pilih apa pun di dropdown ROLES
//         cy.get('select#roles.form-control').should('have.value', ''); // Asumsikan default value kosong


//         // Klik pada dropdown untuk membuka daftar opsi REGION
//         cy.get('select#region.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#region.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#region.form-control').select('PT MITRA MAHKOTA BUANA', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi UNIT
//         cy.get('select#unit.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#unit.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#unit.form-control').select('[DMK] DEMAK', { force: true });

//         // Nama file yang akan diunggah
//         const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

//         // Pilih elemen input file berdasarkan class .form-control
//         cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
//         .attachFile(fileName); // Mengunggah file dari folder "fixtures"

//         cy.get('input#password').type('56789');
//         cy.get('input#confirm_password').type('56789');
//         cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
//         .should('be.visible')
//         .and('contain', 'Password sama');
//         cy.wait(500);
//         cy.contains('SIMPAN').click();

//         // Verifikasi bahwa form tidak terkirim karena validasi required
//         cy.get('select#roles.form-control')
//         .then(($dropdown) => {
//         const dropdownElement = $dropdown[0]; // Ambil elemen DOM mentah
//         expect(dropdownElement.checkValidity()).to.be.false; // Pastikan validasi gagal
//         expect(dropdownElement.validationMessage).to.eq('Please select an item.'); // Sesuaikan pesan jika custom
//       });
//     });

//     it('Negatif Case: Empty field Regions', () => {
//         cy.contains('TAMBAH').click();
//         // Enter valid credentials
//         cy.get('input[name="nik"]').type('1978.MTK.1223');
//         cy.get('input[name="nama"]').type('Icha')

//         // Klik pada dropdown untuk membuka daftar opsi JABATAN
//         cy.get('select#jabatan.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#jabatan.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#jabatan.form-control').select('DIREKTUR UTAMA', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi ROLES
//         cy.get('select#roles.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#roles.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#roles.form-control').select('ADMIN', { force: true });

//         // Jangan pilih apa pun di dropdown REGION
//         cy.get('select#region.form-control').should('have.value', 'PILIH'); // Asumsikan default value kosong

//         // Klik pada dropdown untuk membuka daftar opsi UNIT
//         cy.get('select#unit.form-control').should('have.text', 'PILIH');


//         // Nama file yang akan diunggah
//         const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

//         // Pilih elemen input file berdasarkan class .form-control
//         cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
//         .attachFile(fileName); // Mengunggah file dari folder "fixtures"

//         cy.get('input#password').type('56789');
//         cy.get('input#confirm_password').type('56789');
//         cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
//         .should('be.visible')
//         .and('contain', 'Password sama');
//         cy.wait(500);
//         cy.contains('SIMPAN').click();

//         // Verifikasi bahwa form tidak terkirim karena validasi required
//         cy.get('select#region.form-control')
//         .then(($dropdown) => {
//         const dropdownElement = $dropdown[0]; // Ambil elemen DOM mentah
//         expect(dropdownElement.checkValidity()).to.be.false; // Pastikan validasi gagal
//         expect(dropdownElement.validationMessage).to.eq('Please select an item.'); // Sesuaikan pesan jika custom
//       });
//     });



//     it('Negatif Case: Password invalid', () => {
//         cy.contains('TAMBAH').click();
//         // Enter valid credentials
//         cy.get('input[name="nik"]').type('1979.MTK.1223');
//         cy.get('input[name="nama"]').type('Nur Widianti');

//         // Klik pada dropdown untuk membuka daftar opsi JABATAN
//         cy.get('select#jabatan.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#jabatan.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#jabatan.form-control').select('ADMIN LOGISTIK', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi ROLES
//         cy.get('select#roles.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#roles.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#roles.form-control').select('ADMIN', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi REGION
//         cy.get('select#region.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#region.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#region.form-control').select('PT MITRA MAHKOTA BUANA', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi UNITclear
//         cy.get('select#unit.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#unit.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#unit.form-control').select('[DMK] DEMAK', { force: true });

//         // Nama file yang akan diunggah
//         const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

//         // Pilih elemen input file berdasarkan class .form-control
//         cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
//         .attachFile(fileName); // Mengunggah file dari folder "fixtures"

//         cy.get('input#password').type('56789');
//         cy.get('input#confirm_password').type('56789');
//         cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
//         .should('be.visible')
//         .and('contain', 'Password sama');
//         cy.contains('SIMPAN').click();

//         // Periksa apakah SweetAlert muncul dengan pesan yang benar
//         cy.get('.swal2-popup').should('be.visible');
//         cy.get('.swal2-title').should('contain', 'Oops...');
//         cy.get('.swal2-html-container').should('contain', 'Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan karakter khusus.');
//     });

//     it('Add duplikat data', () => {
//         // Panggil fungsi login dengan NIK dan password yang valid
//         cy.login('validUser', 'validPassword');

//         // Verifikasi bahwa menu "Master" ada di halaman dashboard
//         cy.get('#sidebar').should('exist').and('be.visible');
//         cy.contains('Master').click();
//         cy.contains('Users').click();

//         cy.contains('TAMBAH').click();
//         // Enter valid credentials
//         // cy.get('input[name="nik"]').type('1979.MTK.1223');
//         //cy.get('input[name="nama"]').type('Nur Widianti');
//         const nik = '1979.MTK.1223'; // Bisa diubah sesuai kebutuhan
//         const nama = 'Nur Widianti';
//         cy.get('input[name="nik"]').type(nik); // Ganti dengan selector input username yang sesuai
//         cy.get('input[name="nama"]').type(nama); // Ganti dengan selector input nama yang sesuai

//         // Klik pada dropdown untuk membuka daftar opsi JABATAN
//         cy.get('select#jabatan.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#jabatan.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#jabatan.form-control').select('ADMIN LOGISTIK', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi ROLES
//         cy.get('select#roles.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#roles.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#roles.form-control').select('ADMIN', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi REGION
//         cy.get('select#region.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#region.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#region.form-control').select('PT MITRA MAHKOTA BUANA', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi UNITclear
//         cy.get('select#unit.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#unit.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#unit.form-control').select('[DMK] DEMAK', { force: true });

//         // Nama file yang akan diunggah
//         const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

//         // Pilih elemen input file berdasarkan class .form-control
//         cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
//         .attachFile(fileName); // Mengunggah file dari folder "fixtures"

//         cy.get('input#password').type('Nurwidianti35_@');
//         cy.get('input#confirm_password').type('Nurwidianti35_@');
//         cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
//         .should('be.visible')
//         .and('contain', 'Password sama');
//         cy.contains('SIMPAN').click();

//         // Periksa apakah SweetAlert muncul dengan pesan kesalahan yang benar
//         cy.get('.swal2-popup').should('be.visible');
//         cy.get('.swal2-title').should('contain', 'GAGAL');
//         cy.get('.swal2-html-container').should('contain', `User dengan username ${nik} a/n ${nama} sudah ada`);
//     });

//     it('Add valid data', () => {
//         // Panggil fungsi login dengan NIK dan password yang valid
//         cy.login('validUser', 'validPassword');

//         // Verifikasi bahwa menu "Master" ada di halaman dashboard
//         cy.get('#sidebar').should('exist').and('be.visible');
//         cy.contains('Master').click();
//         cy.contains('Users').click();

//         cy.contains('TAMBAH').click();
//         // Enter valid credentials
//         // cy.get('input[name="nik"]').type('1979.MTK.1223');
//         //cy.get('input[name="nama"]').type('Nur Widianti');
//         const nik = '1979.MTK.1224'; // Bisa diubah sesuai kebutuhan
//         const nama = 'Icha';
//         cy.get('input[name="nik"]').type(nik); // Ganti dengan selector input username yang sesuai
//         cy.get('input[name="nama"]').type(nama); // Ganti dengan selector input nama yang sesuai

//         // Klik pada dropdown untuk membuka daftar opsi JABATAN
//         cy.get('select#jabatan.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#jabatan.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#jabatan.form-control').select('ADMIN LOGISTIK', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi ROLES
//         cy.get('select#roles.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#roles.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#roles.form-control').select('ADMIN', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi REGION
//         cy.get('select#region.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#region.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#region.form-control').select('PT MITRA MAHKOTA BUANA', { force: true });

//         // Klik pada dropdown untuk membuka daftar opsi UNITclear
//         cy.get('select#unit.form-control option:selected').should('have.text', 'PILIH');
//         // Pastikan dropdown terlihat
//         cy.get('select#unit.form-control').scrollIntoView().should('be.visible');
//         // Pilih opsi dari dropdown dengan memaksa jika diperlukan
//         cy.get('select#unit.form-control').select('[DMK] DEMAK', { force: true });

//         // Nama file yang akan diunggah
//         const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

//         // Pilih elemen input file berdasarkan class .form-control
//         cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
//         .attachFile(fileName); // Mengunggah file dari folder "fixtures"

//         cy.get('input#password').type('Nurwidianti35_@');
//         cy.get('input#confirm_password').type('Nurwidianti35_@');
//         cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
//         .should('be.visible')
//         .and('contain', 'Password sama');
//         cy.contains('SIMPAN').click();

//         // Periksa apakah SweetAlert muncul dengan pesan kesalahan yang benar
//         cy.get('.swal2-popup').should('be.visible');
//         cy.get('.swal2-title').should('contain', 'GAGAL');
//         cy.get('.swal2-html-container').should('contain', `User dengan username ${nik} a/n ${nama} sudah ada`);
// });
});
