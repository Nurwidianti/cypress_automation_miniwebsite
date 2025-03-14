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
describe('Edit User test', () => {

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

      it('Edit field name with valid credential', () => {


        cy.get('a[href^="http://localhost:8000/user/"][href$="/edit"].btn-primary').first().click();
        cy.get('input[name="nama"]').clear().should('have.value', '').type('SISWO');
        cy.wait(500);
        cy.contains('SIMPAN').click();
        /*
        // Enter valid credentials



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

        // Klik pada dropdown untuk membuka daftar opsi REGION
        cy.get('select#region.form-control option:selected').should('have.text', 'PILIH');
        // Pastikan dropdown terlihat
        cy.get('select#region.form-control').scrollIntoView().should('be.visible');
        // Pilih opsi dari dropdown dengan memaksa jika diperlukan
        cy.get('select#region.form-control').select('PT MITRA MAHKOTA BUANA', { force: true });

        // Klik pada dropdown untuk membuka daftar opsi UNITclear
        cy.get('select#unit.form-control option:selected').should('have.text', 'PILIH');
        // Pastikan dropdown terlihat
        cy.get('select#unit.form-control').scrollIntoView().should('be.visible');
        // Pilih opsi dari dropdown dengan memaksa jika diperlukan
        cy.get('select#unit.form-control').select('[DMK] DEMAK', { force: true });

        // Nama file yang akan diunggah
        const fileName = 'download.jpeg'; // Ganti dengan nama file Anda

        // Pilih elemen input file berdasarkan class .form-control
        cy.get('input.form-control[type="file"]') // Selector untuk tombol Choose File
        .attachFile(fileName); // Mengunggah file dari folder "fixtures"

        cy.get('input#password').type('56789');
        cy.get('input#confirm_password').type('12345');
        cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
        .should('be.visible')
        .and('contain', 'Password tidak sama');
      */
    });

    it('Negatife Case: empty field name ', () => {
        cy.get('a[href^="http://localhost:8000/user/"][href$="/edit"].btn-primary').first().click();

        cy.get('input[name="nama"]').clear().should('have.value', '');
        cy.wait(500);
        cy.contains('SIMPAN').click();

        // Verifikasi bahwa swal muncul dengan notifikasi gagal
        cy.get('.swal2-popup').should('be.visible');
        cy.get('.swal2-title').should('contain', 'GAGAL'); // Sesuaikan dengan pesan error yang muncul
        cy.get('.swal2-html-container').should('contain', 'Nama tidak boleh kosong'); // Sesuaikan dengan pesan error yang muncul
    });
});
