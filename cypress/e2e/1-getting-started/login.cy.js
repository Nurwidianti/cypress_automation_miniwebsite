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
describe('Login Page Menu Test', () => {
    beforeEach(() => {
      // Visit the login page before each test
      cy.visit('http://localhost:8000/login');
    });

    it('Should display the login page correctly', () => {
        // Check if the page contains the word 'Login'
        cy.contains('Login')

        // Verify the presence of the hidden CSRF token
        cy.get('input[type="hidden"][name="_token"]')
          .should('exist') // Ensure the element exists
          .and('have.attr', 'value') // Ensure the token has a value
          .and('not.be.empty'); // Ensure the value is not empty

           // Verify if the login form is visible
        cy.get('input.form-control').should('be.visible');

        // Verify the presence of username and password fields
        cy.get('input[name="nik"]').should('exist').and('be.visible');
        cy.get('input[name="password"]').should('exist').and('be.visible');

        // Verify the login button
        cy.get('button[type="submit"]').should('exist').and('contain', 'Login');
      });

    it('User can not login with invalid NIK credential', () => {
      // Attempt to login with invalid credentials
      cy.get('input[name="nik"]').type('1978.MTK.1225');
      cy.get('input[name="password"]').type('123456');
      cy.get('button[type="submit"]').click();

      // Verifikasi bahwa field NIK mendapatkan class "is-invalid"
      cy.get('input[name="nik"]').should('have.class', 'is-invalid');
    });

    it('User can not login with invalid Pass credential', () => {
        // Attempt to login with invalid credentials
        cy.get('input[name="nik"]').type('1978.MTK.1222');
        cy.get('input[name="password"]').type('456');
        cy.get('button[type="submit"]').click();

        // Verifikasi bahwa field NIK mendapatkan class "is-invalid"
        cy.get('input[name="name"]').should('have.class', 'is-invalid');
      });

    it('should login successfully with valid credentials', () => {
      // Enter valid credentials
      cy.get('input[name="nik"]').type('1978.MTK.1222'); // Replace with valid username
      cy.get('input[name="password"]').type('123456'); // Replace with valid password
      cy.get('button[type="submit"]').click();

      // Verify successful login (adjust redirection URL or success message)
      cy.url().should('include', '/home'); // Ensure it navigates away from login page
    });
  });



