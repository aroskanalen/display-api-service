describe("Pagination loads", () => {
  it("It loads", () => {
    cy.visit("localhost:3000/");
    cy.get(".pagination").should("not.be.empty");
  });

  it("It works", () => {
    cy.visit("localhost:3000/");
    cy.get(".pagination")
      .find("button")
      .first()
      .invoke("text")
      .should("match", /^1/);
    cy.get("tbody").find("tr").should("have.length", 10);
    cy.get(".pagination").find("button").last().click();
    cy.get("tbody").find("tr").should("have.length", 3);
  });
});
