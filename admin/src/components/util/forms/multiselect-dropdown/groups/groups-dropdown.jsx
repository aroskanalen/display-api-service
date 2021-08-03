import React, { useEffect, useState } from "react";
import PropTypes from "prop-types";
import { useTranslation } from "react-i18next";
import MultiSelectComponent from "../multi-dropdown";

/**
 * @param {object} props
 * the props.
 * @param {Function} props.handleGroupsSelection
 * The callback when an option is selected
 * @param {Array} props.selected
 * The selected options
 * @param {string} props.name
 * The id of the form element
 * @param {Array} props.errors
 * A list of errors, or null.
 * @returns {object}
 * The multidropdown of groups.
 */
function GroupsDropdown({ handleGroupsSelection, selected, name, errors }) {
  const { t } = useTranslation("common");
  const [options, setOptions] = useState();
  const [isLoading, setIsLoading] = useState(true);

  /**
   * Load content from fixture.
   */
  useEffect(() => {
    // @TODO load real content.
    fetch("/fixtures/groups/groups.json")
      .then((response) => response.json())
      .then((jsonData) => {
        setOptions(jsonData.groups);
        setIsLoading(false);
      });
  }, []);

  return (
    <>
      {options && (
        <>
          <MultiSelectComponent
            errors={errors}
            handleSelection={handleGroupsSelection}
            options={options}
            label={t("groups-dropdown.label")}
            selected={selected}
            name={name}
            isLoading={isLoading}
            noSelectedString={t("groups-dropdown.nothing-selected")}
          />
        </>
      )}
    </>
  );
}

GroupsDropdown.defaultProps = {
  errors: null,
};

GroupsDropdown.propTypes = {
  handleGroupsSelection: PropTypes.func.isRequired,
  selected: PropTypes.arrayOf(
    PropTypes.shape({
      value: PropTypes.string,
      label: PropTypes.number,
      disabled: PropTypes.bool,
    })
  ).isRequired,
  name: PropTypes.string.isRequired,
  errors: PropTypes.arrayOf(PropTypes.string),
};

export default GroupsDropdown;
