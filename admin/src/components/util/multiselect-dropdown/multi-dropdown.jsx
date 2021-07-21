import React from "react";
import MultiSelect from "react-multi-select-component";
import PropTypes from "prop-types";

/**
 * @param {object} props
 * the props.
 * @param {Array} props.options
 * The option for the searchable dropdown.
 * @param {Function} props.handleTagSelection
 * The callback when an option is selected
 * @param {Array} props.selected
 * The selected options
 * @param {Boolean} props.isCreatable
 * Whether the multi dropdown can create new options.
 * @returns {object}
 * The multidropdown
 */
function MultiSelectComponent({
  options,
  handleTagSelection,
  selected,
  isCreatable,
}) {
  /**
   * @param {Array} optionsToFilter
   * The options to filter in
   * @param {string} filter
   * The string to filter by
   * @returns {Array}
   * Array of matching values
   */
  function filterOptions(optionsToFilter, filter) {
    if (!filter) {
      return optionsToFilter;
    }
    const re = new RegExp(filter, "i");
    return optionsToFilter.filter(({ label }) => label && label.match(re));
  }

  return (
    <MultiSelect
      isCreatable={isCreatable}
      options={options}
      value={selected}
      filterOptions={filterOptions}
      onChange={handleTagSelection}
    />
  );
}

MultiSelectComponent.defaultProps = {
  isCreatable: false,
};

MultiSelectComponent.propTypes = {
  options: PropTypes.arrayOf(
    PropTypes.shape({
      value: PropTypes.string,
      label: PropTypes.number,
      disabled: PropTypes.bool,
    })
  ).isRequired,
  handleTagSelection: PropTypes.func.isRequired,
  selected: PropTypes.arrayOf(
    PropTypes.shape({
      value: PropTypes.string,
      label: PropTypes.number,
      disabled: PropTypes.bool,
    })
  ).isRequired,
  isCreatable: PropTypes.bool,
};

export default MultiSelectComponent;
