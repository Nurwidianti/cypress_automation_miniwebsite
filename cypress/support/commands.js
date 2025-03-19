// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
import 'cypress-file-upload';
    Cypress.on('uncaught:exception', (err, runnable) => {
    // Jangan menghentikan tes meskipun ada exception
    return false;
});

// Fungsi login yang bisa digunakan di seluruh tes
Cypress.Commands.add('login', (nik, password) => {
    cy.visit('http://localhost:8000/login'); // Kunjungi halaman login

    // Isi form login dengan NIK dan password yang diberikan
    cy.get('input[name="nik"]').type('1978.MTK.1222');
    cy.get('input[name="password"]').type('123456');

    // Klik tombol login
    cy.get('button[type="submit"]').click();

    // Verifikasi bahwa URL mengarah ke halaman dashboard setelah login
    cy.url().should('include', '/home'); // Pastikan diarahkan ke /home
});

// Buat fungsi untuk validasi Region & Unit dengan error handling
Cypress.Commands.add("validateRegionUnit", (region, unit) => {
    // Pilih Region
    cy.get('select#region').select(region);

    // Tunggu perubahan Unit
    cy.wait(500); // Sesuaikan jika perlu

    // Ambil semua opsi dari dropdown Unit secara dinamis
    cy.get('select#unit option').then((options) => {
        const actualOptions = [...options]
            .map(o => o.innerText.trim())
            .filter(text => text !== 'PILIH'); // Hapus opsi default 'PILIH'

        // Log daftar unit yang didapat (opsional untuk debugging)
        cy.log('Daftar unit untuk region:', region, actualOptions);

        // Pastikan daftar unit tidak kosong
        expect(actualOptions).to.not.be.empty;

        // **Jika unit yang ingin dipilih tidak ada dalam daftar, tampilkan error**
        if (!actualOptions.includes(unit)) {
            throw new Error(`Unit "${unit}" tidak ditemukan dalam daftar unit untuk region "${region}".\nUnit yang tersedia: ${actualOptions.join(', ')}`);
        }

        // Pilih unit jika valid
        cy.get('select#unit').select(unit);
    });
});
