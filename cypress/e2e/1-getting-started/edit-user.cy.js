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

    });

    it('Negatife Case: empty field name ', () => {
        cy.get('a[href^="http://localhost:8000/user/4"][href$="/edit"].btn-primary').first().click();

        cy.get('input[name="nama"]').clear().should('have.value', '');
        cy.wait(500);
        cy.contains('SIMPAN').click();

    //     // Verifikasi bahwa swal muncul dengan notifikasi gagal
    //     cy.get('.swal2-popup').should('be.visible');
    //     cy.get('.swal2-title').should('contain', 'GAGAL'); // Sesuaikan dengan pesan error yang muncul
    //     cy.get('.swal2-html-container').should('contain', 'Nama tidak boleh kosong'); // Sesuaikan dengan pesan error yang muncul
    // });


    // it('Negatife Case: empty field name ', () => {
    //     cy.get('a[href^="http://localhost:8000/user/"][href$="/edit"].btn-primary').first().click();

    //     cy.get('input[name="nama"]').clear().should('have.value', '');
    //     cy.wait(500);
    //     cy.contains('SIMPAN').click();

    //     // Verifikasi bahwa swal muncul dengan notifikasi gagal
    //     cy.get('.swal2-popup').should('be.visible');
    //     cy.get('.swal2-title').should('contain', 'GAGAL'); // Sesuaikan dengan pesan error yang muncul
    //     cy.get('.swal2-html-container').should('contain', 'Nama tidak boleh kosong'); // Sesuaikan dengan pesan error yang muncul
    // });

});
