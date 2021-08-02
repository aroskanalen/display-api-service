import { React, useState } from "react";
import PropTypes from "prop-types";
import { Button } from "react-bootstrap";
import { useIntl, FormattedMessage } from "react-intl";
import ListButton from "../list/list-button";
import SlidesDropdown from "../forms/multiselect-dropdown/slides/slides-dropdown";
import Table from "../table/table";
import InfoModal from "../../info-modal/info-modal";
import Published from "../../slides-list/published";
/**
 * A multiselect and table for slides.
 *
 * @param {string} props
 * the props.
 * @param {string} props.name
 * The name for the input
 * @param {string} props.selectedData
 * The data for the multidropdown.
 * @param {Array} props.errors
 * A list of errors, or null.
 * @returns {object}
 * An input.
 */
function SelectSlidesTable({ handleChange, name, selectedData, errors }) {
  const intl = useIntl();
  const [onPlaylists, setOnPlaylists] = useState();
  const [showInfoModal, setShowInfoModal] = useState(false);
  const infoModalText = intl.formatMessage({
    id: "slide_on_the_following_playlists",
  });

  /**
   * @param {Array} playlistArray
   * The array of playlists.
   */
  function openInfoModal(playlistArray) {
    setOnPlaylists(playlistArray);
    setShowInfoModal(true);
  }

  /**
   * Closes the info modal.
   */
  function onCloseInfoModal() {
    setShowInfoModal(false);
    setOnPlaylists();
  }

  /**
   * Removes slide from list of slides.
   *
   * @param {object} props
   * The props.
   * @param {string} props.id
   * The id of the slide
   */
  function removeFromList({ id }) {
    const indexOfItemToRemove = selectedData
      .map((item) => {
        return item.id;
      })
      .indexOf(id);
    selectedData.splice(indexOfItemToRemove, 1);
    const target = { value: selectedData, id: name };
    handleChange({ target });
  }

  const columns = [
    {
      path: "name",
      sort: true,
      label: intl.formatMessage({ id: "table_header_name" }),
    },
    {
      path: "template",
      sort: true,
      label: intl.formatMessage({ id: "table_header_template" }),
    },
    {
      sort: true,
      path: "playlists",
      content: (data) =>
        ListButton(
          openInfoModal,
          data.playlists,
          data.playlists?.length,
          data.playlists?.length === 0
        ),
      key: "playlists",
      label: intl.formatMessage({ id: "table_header_number_of_playlists" }),
    },
    {
      path: "tags",
      sort: true,
      label: intl.formatMessage({ id: "table_header_tags" }),
    },
    {
      path: "published",
      sort: true,
      content: (data) => Published(data),
      label: intl.formatMessage({ id: "table_header_published" }),
    },
    {
      key: "delete",
      content: (slideData) => (
        <Button variant="danger" onClick={() => removeFromList(slideData)}>
          <FormattedMessage
            id="remove_from_list"
            defaultMessage="remove_from_list"
          />
        </Button>
      ),
    },
  ];

  return (
    <>
      <SlidesDropdown
        errors={errors}
        name={name}
        handleSlideSelection={handleChange}
        selected={selectedData}
      />
      {selectedData?.length > 0 && (
        <Table columns={columns} data={selectedData} />
      )}
      <InfoModal
        show={showInfoModal}
        onClose={onCloseInfoModal}
        dataStructureToDisplay={onPlaylists}
        infoModalString={infoModalText}
      />
    </>
  );
}

SelectSlidesTable.defaultProps = {
  errors: [],
  selectedData: [],
};

SelectSlidesTable.propTypes = {
  name: PropTypes.string.isRequired,
  selectedData: PropTypes.arrayOf(
    PropTypes.shape({ value: PropTypes.number, label: PropTypes.string })
  ),
  handleChange: PropTypes.func.isRequired,
  errors: PropTypes.arrayOf(PropTypes.string),
};

export default SelectSlidesTable;