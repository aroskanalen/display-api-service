import { React } from "react";
import { FormattedMessage } from "react-intl";
import { Navbar, Nav, NavDropdown, Container } from "react-bootstrap";
import { Link } from "react-router-dom";

/**
 * The side bar component.
 *
 * @returns {object}
 *   The NavBar
 */
function NavBar() {
  return (
    <Navbar bg="light" expand="lg" className="left">
      <Container>
        <Navbar.Toggle aria-controls="basic-navbar-nav" />
        <Navbar.Collapse id="basic-navbar-nav">
          <Nav className="me-auto">
            <NavDropdown
              title={
                <FormattedMessage
                  id="navbar_content"
                  defaultMessage="navbar_content"
                />
              }
              id="navbar_content"
            >
              <NavDropdown.Item>
                <Link
                  className="navbar-brand"
                  id="navbar_content_tags"
                  style={{ color: "black" }}
                  to="/tags"
                >
                  <FormattedMessage
                    id="navbar_tags"
                    defaultMessage="navbar_tags"
                  />
                </Link>
              </NavDropdown.Item>
              <NavDropdown.Divider />
              <NavDropdown.Item>
                <Link
                  className="navbar-brand"
                  id="navbar_content_media"
                  style={{ color: "black" }}
                  to="/media-list"
                >
                  <FormattedMessage
                    id="navbar_media"
                    defaultMessage="navbar_media"
                  />
                </Link>
              </NavDropdown.Item>
            </NavDropdown>

            <Nav.Item>
              <Link
                className="navbar-brand"
                id="navbar_slides"
                style={{ color: "black" }}
                to="/slides"
              >
                <FormattedMessage
                  id="navbar_slides"
                  defaultMessage="navbar_slides"
                />
              </Link>
            </Nav.Item>
            <NavDropdown
              title={
                <FormattedMessage
                  id="navbar_screens"
                  defaultMessage="navbar_screens"
                />
              }
              id="navbar_screens"
            >
              <NavDropdown.Item>
                <Link
                  className="navbar-brand"
                  id="navbar_screens_screens"
                  style={{ color: "black" }}
                  to="/screens"
                >
                  <FormattedMessage
                    id="navbar_screens"
                    defaultMessage="navbar_screens"
                  />
                </Link>
              </NavDropdown.Item>
              <NavDropdown.Divider />
              <NavDropdown.Item>
                <Link
                  className="navbar-brand"
                  id="navbar_screens_groups"
                  style={{ color: "black" }}
                  to="/groups"
                >
                  <FormattedMessage
                    id="navbar_groups"
                    defaultMessage="navbar_groups"
                  />
                </Link>
              </NavDropdown.Item>
              <NavDropdown.Divider />
              <NavDropdown.Item>
                <Link
                  className="navbar-brand"
                  id="navbar_screens_locations"
                  style={{ color: "black" }}
                  to="/locations"
                >
                  <FormattedMessage
                    id="navbar_locations"
                    defaultMessage="navbar_locations"
                  />
                </Link>
              </NavDropdown.Item>
            </NavDropdown>
            <NavDropdown
              title={
                <FormattedMessage
                  id="navbar_playlists"
                  defaultMessage="navbar_playlists"
                />
              }
              id="navbar_playlists"
            >
              <NavDropdown.Item>
                <Link
                  className="navbar-brand"
                  id="navbar_playlists_playlists"
                  style={{ color: "black" }}
                  to="/playlists"
                >
                  <FormattedMessage
                    id="navbar_playlists"
                    defaultMessage="navbar_playlists"
                  />
                </Link>
              </NavDropdown.Item>
              <NavDropdown.Divider />
              <NavDropdown.Item>
                <Link
                  className="navbar-brand"
                  id="navbar_playlists_categories"
                  style={{ color: "black" }}
                  to="/categories"
                >
                  <FormattedMessage
                    id="navbar_categories"
                    defaultMessage="navbar_categories"
                  />
                </Link>
              </NavDropdown.Item>
            </NavDropdown>
          </Nav>
        </Navbar.Collapse>
      </Container>
    </Navbar>
  );
}

export default NavBar;
