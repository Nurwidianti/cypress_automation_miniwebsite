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


});