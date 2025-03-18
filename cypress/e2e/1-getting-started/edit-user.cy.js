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

        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();
        cy.get('input[name="nama"]').clear().should('have.value', '').type('TEST');
        cy.wait(500);
        cy.contains('SIMPAN').click();
        //Verifikasi bahwa swal muncul dengan notifikasi berhasil
        cy.get('.swal2-popup').should('be.visible');
        cy.get('#swal2-title.swal2-title').should('contain', 'BERHASIL');
        cy.get('.swal2-html-container').should('contain', 'User berhasil diedit');

    });

    it('Negatife Case: empty field name ', () => {
        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();
        cy.get('input[name="nama"]').clear().should('have.value', '');
        cy.wait(500);
        cy.contains('SIMPAN').click();

    // Verifikasi bahwa swal muncul dengan notifikasi gagal
        cy.get('.swal2-popup').should('be.visible');
        cy.get('#swal2-title.swal2-title').should('contain', 'GAGAL'); // Sesuaikan dengan pesan error yang muncul
        cy.get('.swal2-html-container').should('contain', 'Nama tidak boleh kosong'); // Sesuaikan dengan pesan error yang muncul
    });


    it('Negatife Case: empty field NIK ', () => {
        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();
        cy.get('input[name="nik"]').clear().should('have.value', '');
        cy.get('input[name="nama"]').type('TEST AKUN')
        cy.wait(500);
        cy.contains('SIMPAN').click();

    //Verifikasi bahwa swal muncul dengan notifikasi gagal
        cy.get('.swal2-popup').should('be.visible');
        cy.get('#swal2-title.swal2-title').should('contain', 'GAGAL'); // Sesuaikan dengan pesan error yang muncul
        cy.get('.swal2-html-container').should('contain', 'Nama tidak boleh kosong'); // Sesuaikan dengan pesan error yang muncul
    });

    it('Negatif Case: Different input Password dan Confirm Password ', () => {
        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();
        cy.get('input#password').type('56789');
        cy.get('input#confirm_password').type('12345');
        cy.get('span#message') // Sesuaikan dengan selector elemen <span> error Anda
        .should('be.visible')
        .and('contain', 'Password tidak sama');
        cy.wait(500);
        cy.contains('SIMPAN').click();
    });

    it('User can Edit Jabatan user', () => {
        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();
        cy.get('input[name="nik"]').clear().type('1978.MTK.1224')
        cy.get('input[name="nama"]').clear().type('TEST AKUN')
        // Pastikan dropdown "jabatan" tersedia dan terlihat
        cy.get('select#jabatan').should('be.visible');
        // Pilih salah satu opsi dalam dropdown Jabatan
        cy.get('select#jabatan').select('ADMIN PRODUKSI');
        cy.wait(500);
        cy.get('select#jabatan.form-control option:selected').should('have.text', 'ADMIN PRODUKSI');
        cy.get('select#jabatan.form-control').select('STAFF REGION', { force: true });
        cy.contains('SIMPAN').click();

        //Verifikasi bahwa swal muncul dengan notifikasi berhasil
        cy.get('.swal2-popup').should('be.visible');
        cy.get('#swal2-title.swal2-title').should('contain', 'BERHASIL'); // Sesuaikan dengan pesan error yang muncul
        cy.get('.swal2-html-container').should('contain', 'User berhasil diedit'); // Sesuaikan dengan pesan error yang muncul
        cy.wait(1500);
        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();
        cy.get('select#jabatan').should('have.value', 'STAFF REGION');
    });

    it('User can Edit Roles user', () => {
        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();

        // Pastikan dropdown "jabatan" tersedia dan terlihat
        cy.get('select#roles').should('be.visible');
        // Pilih salah satu opsi dalam dropdown Jabatan
        cy.get('select#roles').select('REGION');
        cy.wait(500);
        // cy.get('select#role.form-control option:selected').should('have.text', 'ADMIN PRODUKSI');
        // cy.get('select#jabatan.form-control').select('STAFF REGION', { force: true });
        cy.contains('SIMPAN').click();

        //Verifikasi bahwa swal muncul dengan notifikasi berhasil
        cy.get('.swal2-popup').should('be.visible');
        cy.get('#swal2-title.swal2-title').should('contain', 'BERHASIL'); // Sesuaikan dengan pesan error yang muncul
        cy.get('.swal2-html-container').should('contain', 'User berhasil diedit'); // Sesuaikan dengan pesan error yang muncul
        cy.wait(1500);
        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();
        cy.get('select#roles').should('have.value', 'region');
    });

    // it('Validate Unit Dropdown Updates When Region is Changed (Edit User)', () => {
    //     cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();

    //     // Pastikan dropdown "jabatan" tersedia dan terlihat
    //     cy.get('select#region').should('be.visible');
    //     // Pilih salah satu opsi dalam dropdown Jabatan
    //     cy.get('select#region').select('PT BINTANG TERANG BERSINAR');
    //     cy.wait(500);
    //     cy.get('select#region').select('PT GILAR PERWIRA SATRIA');
    //     // cy.get('select#role.form-control option:selected').should('have.text', 'ADMIN PRODUKSI');
    //     // cy.get('select#jabatan.form-control').select('STAFF REGION', { force: true });
    //     cy.contains('SIMPAN').click();

    //     //Verifikasi bahwa swal muncul dengan notifikasi berhasil
    //     cy.get('.swal2-popup').should('be.visible');
    //     cy.get('#swal2-title.swal2-title').should('contain', 'BERHASIL'); // Sesuaikan dengan pesan error yang muncul
    //     cy.get('.swal2-html-container').should('contain', 'User berhasil diedit'); // Sesuaikan dengan pesan error yang muncul
    //     cy.wait(1500);
    //     cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();
    //     cy.get('select#region').should('have.value', 'PT GILAR PERWIRA SATRIA');
    // });




});
