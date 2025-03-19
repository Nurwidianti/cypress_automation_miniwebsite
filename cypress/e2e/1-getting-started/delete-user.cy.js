/// <reference types="cypress" />
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
     it('Menghapus data dari tabel', () => {
        // Pilih baris berdasarkan nama atau data unik lainnya
        cy.contains('TEST AKUN') // Ganti dengan data unik di tabel
          .parents('tr') // Pilih baris yang sesuai
          .within(() => {
            cy.get('button').contains('🗑') // Sesuaikan dengan ikon hapus
              .click(); // Klik tombol hapus
          });
        // Verifikasi bahwa data sudah terhapus
        cy.contains('TEST AKUN').should('not.exist');
      });

});