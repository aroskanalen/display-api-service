import React from "react";
import PropTypes from "prop-types";

/**
 * An input for forms.
 *
 * @param {string} props
 * the props.
 * @param {string} props.radioGroupNamenpm install react-dnd react-dnd-html5-backend
 * The name of the input
 * @param {string} props.label
 * The label for the input
 * @returns {object}
 * An input.
 */
function RadioButtons({
  radioGroupName,
  label,
  selected,
  options,
  handleChange,
}) {
  /**
   * @param {object}  props
   * The props
   * @param {object} props.target
   * The object containing the values to via callback return.
   */
  function onCheckedRadio({ target }) {
    const returnTarget = { value: target.id, id: target.name };
    handleChange({ target: returnTarget });
  }

  return (
    <>
      <label htmlFor={`radios-${label}`}>{label}</label>
      <div id={`radios-${label}`}>
        {options.map(({ id, label: radioLabel }) => (
          <div className="form-check" key={id}>
            <input
              className="form-check-input"
              type="radio"
              name={radioGroupName}
              id={id}
              checked={selected === id}
              onChange={onCheckedRadio}
            />
            <label className="form-check-label" htmlFor={id}>
              {radioLabel}
            </label>
          </div>
        ))}
      </div>
    </>
  );
}

RadioButtons.propTypes = {
  radioGroupName: PropTypes.string.isRequired,
  label: PropTypes.string.isRequired,
  selected: PropTypes.string.isRequired,
  options: PropTypes.arrayOf(
    PropTypes.shape({
      id: PropTypes.string.isRequired,
      label: PropTypes.string.isRequired,
    })
  ).isRequired,
  handleChange: PropTypes.func.isRequired,
};

export default RadioButtons;
