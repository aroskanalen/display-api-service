import { React, useState, useEffect } from "react";
import { useParams, Redirect } from "react-router";
import { Container, Form, Button } from "react-bootstrap";
import { useHistory } from "react-router-dom";
import { useIntl, FormattedMessage } from "react-intl";
import FormInput from "../util/forms/form-input";
import LocationDropdown from "../util/multiselect-dropdown/locations/location-dropdown";
import FormInputArea from "../util/forms/form-input-area";
/**
 * The edit screen component.
 *
 * @returns {object}
 *   The edit screen page.
 */
function EditScreen() {
  const intl = useIntl();
  const history = useHistory();
  const [formStateObject, setFormStateObject] = useState({ locations: [] });
  const { id } = useParams();
  const [screen, setScreen] = useState([]);
  const [screenName, setScreenName] = useState("");
  const [submitted, setSubmitted] = useState(false);
  const newScreen = id === "new";
  const validText = intl.formatMessage({ id: "valid_text_screen_name_input" });
  const screenLabel = intl.formatMessage({ id: "edit_add_screen_label" });
  const screenPlaceholder = intl.formatMessage({
    id: "edit_add_screen_label_placeholder",
  });
  const [selectedLocations, setSelectedLocations] = useState([]);

  /**
   * Load content from fixture.
   */
  useEffect(() => {
    // @TODO load real content.
    if (!newScreen) {
      fetch("http://localhost:3000/fixtures/screens/screen.json")
        .then((response) => response.json())
        .then((jsonData) => {
          setScreen(jsonData.screen);
          setScreenName(jsonData.screen.name);
        });
    }
  }, []);

  /**
   * Set state on change in input field
   *
   * @param {object} props
   * The props.
   * @param {object} props.target
   * event target
   */
  function handleInput({ target }) {
    target.setCustomValidity("");
    let localFormStateObject = { ...formStateObject };
    localFormStateObject[target.id] = target.value;
    setFormStateObject(localFormStateObject);
  }

  /**
   * Handles validation of input with translation.
   *
   * @param {object} props
   * The props.
   * @param {object} props.target
   * event target
   */
  function handleValidationMessage({ target }) {
    const { message } = target.dataset;
    target.setCustomValidity(message);
  }
  /**
   * Redirects back to list.
   */
  function handleSubmit() {
    setSubmitted(true);
  }

  /**
   * @param {object} location
   * the location to select
   */
  function handleLocationSelection(location) {
    setFormStateObject();
    setSelectedLocations(location);
  }

  return (
    <>
      <Container>
        <div>{JSON.stringify(formStateObject)}</div>
        <Form onSubmit={handleSubmit}>
          {newScreen && (
            <h1>
              <FormattedMessage
                id="create_new_screen"
                defaultMessage="create_new_screen"
              />
            </h1>
          )}
          {!newScreen && (
            <h1>
              <FormattedMessage id="edit_screen" defaultMessage="edit_screen" />
              {screen.name}
            </h1>
          )}
          <LocationDropdown
            formId="locations"
            handleLocationSelection={handleInput}
            selected={selectedLocations}
          />
          <FormInput
            name="name"
            type="text"
            label={screenLabel}
            required
            placeholder={screenPlaceholder}
            value={formStateObject["name"]}
            onChange={handleInput}
            data-message={validText}
            onInvalid={handleValidationMessage}
          />
          <FormInputArea
            name="description"
            type="text"
            label={screenLabel}
            required
            placeholder={screenPlaceholder}
            value={formStateObject["description"]}
            onChange={handleInput}
            data-message={validText}
            onInvalid={handleValidationMessage}
          ></FormInputArea>
          {submitted && <Redirect to="/screens" />}
          <Button
            variant="secondary"
            type="button"
            onClick={() => history.goBack()}
          >
            <FormattedMessage id="cancel" defaultMessage="cancel" />
          </Button>
          <Button variant="primary" type="submit">
            <FormattedMessage id="save_screen" defaultMessage="save_screen" />
          </Button>
        </Form>
      </Container>
    </>
  );
}

export default EditScreen;
