import { React, useEffect, useState } from "react";
import { Button, Col, Container, Row } from "react-bootstrap";
import { FormattedMessage, useIntl } from "react-intl";
import CheckboxForList from "../util/list/checkbox-for-list";
import List from "../util/list/list";
import selectedRowsHelper from "../util/helpers/selectedRowsHelper";
import DeleteModal from "../delete-modal/delete-modal";
import InfoModal from "../info-modal/info-modal";
import Published from "./published";
import LinkForList from "../util/list/link-for-list";
import ListButton from "../util/list/list-button";

/**
 * The category list component.
 *
 * @returns {object}
 * The SlidesList
 */
function SlidesList() {
  const intl = useIntl();
  const [selectedRows, setSelectedRows] = useState([]);
  const [onPlaylists, setOnPlaylists] = useState();
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [showInfoModal, setShowInfoModal] = useState(false);
  const [slides, setSlides] = useState([]);
  const infoModalText = intl.formatMessage({
    id: "slide_on_the_following_playlists",
  });
  /**
   * Load content from fixture.
   */
  useEffect(() => {
    // @TODO load real content.

    fetch(`/fixtures/slides/slides.json`)
      .then((response) => response.json())
      .then((jsonData) => {
        setSlides(jsonData.slides);
      });
  }, []);

  /**
   * Sets the selected row in state.
   *
   * @param {object} data
   * The selected row.
   */
  function handleSelected(data) {
    setSelectedRows(selectedRowsHelper(data, [...selectedRows]));
  }

  /**
   * Opens the delete modal, for deleting row.
   *
   * @param {object} props
   * The props.
   * @param {string} props.name
   * The name of the tag.
   * @param {number} props.id
   * The id of the tag
   */
  function openDeleteModal({ id, name }) {
    setSelectedRows([{ id, name }]);
    setShowDeleteModal(true);
  }

  /**
   * @param {Array} playlistArray
   * The array of playlists.
   */
  function openInfoModal(playlistArray) {
    setOnPlaylists(playlistArray);
    setShowInfoModal(true);
  }

  // The columns for the table.
  const columns = [
    {
      key: "pick",
      label: intl.formatMessage({ id: "table_header_pick" }),
      content: (data) => (
        <CheckboxForList onSelected={() => handleSelected(data)} />
      ),
    },
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
      path: "onFollowingPlaylists",
      content: (data) =>
        ListButton(
          openInfoModal,
          data.onFollowingPlaylists,
          data.onFollowingPlaylists.length,
          data.onFollowingPlaylists.length === 0
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
      key: "edit",
      content: (data) => <LinkForList data={data} param="slide" />,
    },
    {
      key: "delete",
      content: (data) => (
        <>
          <div className="m-2">
            <Button
              variant="danger"
              disabled={selectedRows.length > 0}
              onClick={() => openDeleteModal(data)}
            >
              <FormattedMessage id="delete" defaultMessage="delete" />
            </Button>
          </div>
        </>
      ),
    },
  ];

  /**
   * Deletes screen, and closes modal.
   *
   * @param {object} props
   * The props.
   * @param {string} props.name
   * The name of the tag.
   * @param {number} props.id
   * The id of the tag
   */
  // eslint-disable-next-line
  function handleDelete({ id, name }) {
    // @TODO delete element
    setSelectedRows([]);
    setShowDeleteModal(false);
  }

  /**
   * Closes the delete modal.
   */
  function onCloseDeleteModal() {
    setSelectedRows([]);
    setShowDeleteModal(false);
  }

  /**
   * Closes the info modal.
   */
  function onCloseInfoModal() {
    setShowInfoModal(false);
    setOnPlaylists();
  }

  return (
    <Container>
      <Row className="align-items-end mt-2">
        <Col>
          <h1>
            <FormattedMessage
              id="slides_list_header"
              defaultMessage="slides_list_header"
            />
          </h1>
        </Col>
        <Col md="auto">
          <Button>
            <FormattedMessage
              id="create_new_slide"
              defaultMessage="create_new_slide"
            />
          </Button>
        </Col>
      </Row>
      {slides && (
        <List columns={columns} selectedRows={selectedRows} data={slides} />
      )}
      <DeleteModal
        show={showDeleteModal}
        onClose={onCloseDeleteModal}
        handleAccept={handleDelete}
        selectedRows={selectedRows}
      />
      <InfoModal
        show={showInfoModal}
        onClose={onCloseInfoModal}
        dataStructureToDisplay={onPlaylists}
        infoModalString={infoModalText}
      />
    </Container>
  );
}

export default SlidesList;
