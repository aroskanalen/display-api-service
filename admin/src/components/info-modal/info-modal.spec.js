describe("Info modal loads", () => {
  it("It loads", () => {
    cy.visit("/categories");
    cy.get("tbody").find("tr td button").eq(0).should("not.be.disabled");
    cy.get("tbody").find("tr td button").eq(0).click();
    cy.get("#info-modal")
      .invoke("text")
      .should(
        "match",
        /^ObsKategorien er på de følgende spillelister: dynamic, Mandatory, Enhanced, explicit and policyLuk dialog/
      );
  });
});
