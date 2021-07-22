import { React, useState, useEffect } from "react";
import { useParams, Redirect } from "react-router";
import { Container, Form, Button } from "react-bootstrap";
import { useHistory } from "react-router-dom";
import { useIntl, FormattedMessage } from "react-intl";
import FormInput from "../util/forms/form-input";
import LocationDropdown from "../util/forms/multiselect-dropdown/locations/location-dropdown";
import GroupsDropdown from "../util/forms/multiselect-dropdown/groups/groups-dropdown";
import Select from "../util/forms/select";
import FormInputArea from "../util/forms/form-input-area";
import RadioButtons from "../util/forms/radio-buttons";
import PlaylistDragAndDrop from "../playlist-drag-and-drop/playlist-drag-and-drop";
import getFormErrors from "../util/helpers/form-errors-helper";
/**
 * The edit screen component.
 *
 * @returns {object}
 *   The edit screen page.
 */
function EditScreen() {
  const intl = useIntl();

  const history = useHistory();
  const radioButtonOptions = [
    {
      id: "horizontal",
      label: intl.formatMessage({
        id: "horizontal_layout",
      }),
    },
    {
      id: "vertical",
      label: intl.formatMessage({
        id: "vertical_layout",
      }),
    },
  ];
  const [formStateObject, setFormStateObject] = useState({
    screen_locations: [],
    screen_groups: [],
    screen_layout: "",
    playlists: [],
    horizontal_or_vertical: radioButtonOptions[0].id,
  });
  const { id } = useParams();
  const [screenName, setScreenName] = useState([]);
  const [layoutOptions, setLayoutOptions] = useState();
  const [errors, setErrors] = useState([]);
  const [submitted, setSubmitted] = useState(false);
  const newScreen = id === "new";

  /**
   * Load content from fixture.
   */
  useEffect(() => {
    // @TODO load real content.
    if (!newScreen) {
      fetch("/fixtures/screens/screen.json")
        .then((response) => response.json())
        .then((jsonData) => {
          setScreenName(jsonData.screen.name);
          setFormStateObject({
            screen_locations: jsonData.screen.locations,
            sizeOfScreen: jsonData.screen.sizeOfScreen,
            resolutionOfScreen: jsonData.screen.resolutionOfScreen,
            screen_groups: jsonData.screen.groups,
            screen_layout: jsonData.screen.screenLayout,
            playlists: jsonData.screen.playlists,
            horizontal_or_vertical: jsonData.screen.horizontal_or_vertical,
            screen_name: jsonData.screen.name,
            description: jsonData.screen.description,
            descriptionOfLocation: jsonData.screen.descriptionOfLocation,
          });
          // setScreen(jsonData.screen);
        });
    }
    fetch("/fixtures/screen-layout/screen-layout.json")
      .then((response) => response.json())
      .then((jsonData) => {
        setLayoutOptions(jsonData.layouts);
      });
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
    const localFormStateObject = { ...formStateObject };
    localFormStateObject[target.id] = target.value;
    setFormStateObject(localFormStateObject);
  }

  /**
   * Handles validations, and goes back to list.
   *
   * @todo make it save.
   * @param {object} e
   * the submit event.
   * @returns {boolean}
   * Boolean indicating whether to submit form.
   */
  function handleSubmit(e) {
    e.preventDefault();
    setErrors([]);
    let returnValue = false;
    const createdErrors = getFormErrors(formStateObject, "screen");
    if (createdErrors.length > 0) {
      setErrors(createdErrors);
    } else {
      setSubmitted(true);
      returnValue = true;
    }
    return returnValue;
  }

  return (
    <Container>
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
            {screenName}
          </h1>
        )}
        <FormInput
          errors={errors}
          name="screen_name"
          type="text"
          label={intl.formatMessage({ id: "edit_add_screen_label_name" })}
          invalidText={intl.formatMessage({
            id: "edit_add_screen_label_name_invalid",
          })}
          placeholder={intl.formatMessage({
            id: "edit_add_screen_placeholder_name",
          })}
          value={formStateObject.screen_name}
          onChange={handleInput}
        />
        <FormInputArea
          name="description"
          type="text"
          label={intl.formatMessage({
            id: "edit_add_screen_label_description",
          })}
          placeholder={intl.formatMessage({
            id: "edit_add_screen_placeholder_description",
          })}
          value={formStateObject.description}
          onChange={handleInput}
        />
        <GroupsDropdown
          errors={errors}
          name="screen_groups"
          handleGroupsSelection={handleInput}
          selected={formStateObject.screen_groups}
        />
        <LocationDropdown
          errors={errors}
          name="screen_locations"
          handleLocationSelection={handleInput}
          selected={formStateObject.screen_locations}
        />
        {layoutOptions && (
          <Select
            name="screen_layout"
            onChange={handleInput}
            label={intl.formatMessage({
              id: "edit_add_screen_label_screen_layout",
            })}
            errors={errors}
            options={layoutOptions}
            value={formStateObject.screen_layout}
          />
        )}
        <FormInput
          name="descriptionOfLocation"
          type="text"
          label={intl.formatMessage({
            id: "edit_add_screen_label_description_of_location",
          })}
          required
          placeholder={intl.formatMessage({
            id: "edit_add_screen_placeholder_description_of_location",
          })}
          value={formStateObject.descriptionOfLocation}
          onChange={handleInput}
        />
        <FormInput
          name="sizeOfScreen"
          type="text"
          label={intl.formatMessage({
            id: "edit_add_screen_label_size_of_screen",
          })}
          placeholder={intl.formatMessage({
            id: "edit_add_screen_placeholder_size_of_screen",
          })}
          value={formStateObject.sizeOfScreen}
          onChange={handleInput}
        />
        <RadioButtons
          options={radioButtonOptions}
          radioGroupName="horizontal_or_vertical"
          selected={formStateObject.horizontal_or_vertical}
          handleChange={handleInput}
          label={intl.formatMessage({
            id: "edit_add_screen_horizontal_or_vertical_label",
          })}
        />
        <FormInput
          name="resolutionOfScreen"
          type="text"
          label={intl.formatMessage({
            id: "edit_add_screen_label_resolution_of_screen",
          })}
          placeholder={intl.formatMessage({
            id: "edit_add_screen_placeholder_resolution_of_screen",
          })}
          value={formStateObject.resolutionOfScreen}
          helpText={intl.formatMessage({
            id: "edit_add_screen_helptext_resolution_of_screen",
          })}
          pattern="(\d+)x(\d+)"
          onChange={handleInput}
        />
        {/* <PlaylistDragAndDrop
          handleChange={handleInput}
          name="playlists"
          data={formStateObject.playlists}
        /> */}
        {submitted && <Redirect to="/screens" />}
        <Button
          variant="secondary"
          type="button"
          onClick={() => history.goBack()}
        >
          <FormattedMessage id="cancel" defaultMessage="cancel" />
        </Button>
        <Button variant="primary" type="submit" id="save_screen">
          <FormattedMessage id="save_screen" defaultMessage="save_screen" />
        </Button>
      </Form>
    </Container>
  );
}

export default EditScreen;
