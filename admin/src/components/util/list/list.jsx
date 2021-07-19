import { React, useState, useEffect } from "react";
import { Button, Row, Col } from "react-bootstrap";
import { FormattedMessage } from "react-intl";
import { useLocation, useHistory } from "react-router-dom";
import PropTypes from "prop-types";
import Table from "../table/table";
import SearchBox from "../search-box/search-box";
import DeleteModal from "../../delete-modal/delete-modal";
import Pagination from "../paginate/pagination";
import ColumnProptypes from "../../proptypes/column-proptypes";
import SelectedRowsProptypes from "../../proptypes/selected-rows-proptypes";
import MergeModal from "../../merge-modal/merge-modal";

/**
 * @param {object} props
 * The props.
 * @param {Array} props.data
 * The data for the list.
 * @param {Array} props.columns
 * The columns for the table.
 * @param {Array} props.selectedRows
 * The selected rows, for styling.
 * @returns {object}
 * The List.
 */
function List({ data, columns, selectedRows }) {
  const { search } = useLocation();
  const history = useHistory();
  const searchParams = new URLSearchParams(search).get("search");
  const sortParams = new URLSearchParams(search).get("sort");
  const orderParams = new URLSearchParams(search).get("order");
  const pageParams = new URLSearchParams(search).get("page");
  // At least two rows must be selected for merge.
  const disableMergeButton = selectedRows.length < 2;
  // At least one row must be selected for deletion.
  const disableDeleteButton = !selectedRows.length > 0;
  const [searchText, setSearchText] = useState(
    searchParams !== "null" ? searchParams : ""
  );
  const [sortBy, setSortBy] = useState({
    path: sortParams || "name",
    order: orderParams || "asc",
  });
  const pageSize = 10;
  const [currentPage, setCurrentPage] = useState(
    parseInt(pageParams, 10) ? parseInt(pageParams, 10) : 1
  );
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [showMergeModal, setMergeMergeModal] = useState(false);

  /**
   * @param {string} newSearchText
   * Updates the search text state and url.
   */
  function handleSearch(newSearchText) {
    setCurrentPage(1);
    setSearchText(newSearchText);
  }

  /**
   * @param {Array} items
   * The items to paginate.
   * @param {number} pageNumber
   * The chosen page.
   * @param {number} sizeOfPage
   * The page size
   * @returns {Array}
   * The paginated items.
   */
  function paginate(items, pageNumber, sizeOfPage) {
    const startIndex = (pageNumber - 1) * sizeOfPage;
    return items.slice(startIndex, startIndex + sizeOfPage);
  }

  /**
   * If they search or filter, the pagination is reset.
   */
  useEffect(() => {
    const params = new URLSearchParams();
    if (searchText) {
      params.append("search", searchText);
    }
    params.append("sort", sortBy.path);
    params.append("order", sortBy.order);
    params.append("page", currentPage);
    history.replace({ search: params.toString() });
  }, [searchText, sortBy, currentPage]);

  /**
   * Closes delete modal.
   */
  function onCloseDeleteModal() {
    setShowDeleteModal(false);
  }

  /**
   * Closes merge modal.
   */
  function onCloseMergeModal() {
    setMergeMergeModal(false);
  }

  /**
   * @param {number} page
   * Updates pagination page.
   */
  function handlePageChange(page) {
    setCurrentPage(page);
  }

  /**
   * @param {object} sortColumn
   * Updates sortcolumn.
   */
  function handleSort(sortColumn) {
    setCurrentPage(1);
    setSortBy(sortColumn);
  }

  /**
   * @param {object} dataToFilter
   * Search filter function.
   * @returns {boolean}
   * Whether the searchtext is in the data entry.
   */
  function filterDataFromSearchInput(dataToFilter) {
    const dataValuesString = Object.values(dataToFilter).join(" ");
    return dataValuesString
      .toLocaleLowerCase()
      .includes(searchText.toLocaleLowerCase());
  }

  /**
   * @param {string|number} a
   * sort parameter a
   * @param {string|number} b
   * sort parameter b
   * @returns {number}
   * Sorting number.
   */
  function sortData(a, b) {
    let sortVarA = a[sortBy.path];
    let sortVarB = b[sortBy.path];

    sortVarA =
      typeof sortVarA === "string" ? sortVarA.toLocaleLowerCase() : sortVarA;
    sortVarB =
      typeof sortVarB === "string" ? sortVarB.toLocaleLowerCase() : sortVarB;
    if (sortVarA < sortVarB) {
      return -1;
    }
    if (sortVarA > sortVarB) {
      return 1;
    }

    return 0;
  }

  /**
   * @returns {object}
   * returns object of paginated data array and length of data.
   */
  function getTableData() {
    let returnValue = data;
    if (searchText) {
      returnValue = returnValue.filter(filterDataFromSearchInput);
    }
    if (sortBy) {
      returnValue = returnValue.sort(sortData);
    }
    if (sortBy.order === "desc") {
      returnValue = returnValue.reverse();
    }
    const paginated = paginate(returnValue, currentPage, pageSize);
    return { data: paginated, length: returnValue.length };
  }

  /**
   * Deletes selected data, and closes modal.
   */
  function handleDelete() {
    // @TODO delete elements
    setShowDeleteModal(false);
  }

  /**
   * Should handle merge.
   *
   * @param {string} mergeName - the new name for the data.
   */
  function handleMerge(mergeName) {
    // @TODO merge elements and remove console.log
    console.log(mergeName); // eslint-disable-line
    setMergeMergeModal(false);
  }

  return (
    <>
      <Row className="mt-2 mb-2">
        {searchText && (
          <Col>
            <SearchBox value={searchText} onChange={handleSearch} />
          </Col>
        )}
        <Col className="d-flex justify-content-end">
          <div className="ml-4">
            <Button
              variant="danger"
              id="delete-button"
              disabled={disableDeleteButton}
              onClick={() => setShowDeleteModal(true)}
            >
              <FormattedMessage id="delete" defaultMessage="delete" />
            </Button>
          </div>
          <div className="ml-4">
            <Button
              className="ml-2"
              id="merge-button"
              disabled={disableMergeButton}
              onClick={() => setMergeMergeModal(true)}
              variant="success"
            >
              <FormattedMessage id="merge" defaultMessage="merge" />
            </Button>
          </div>
        </Col>
      </Row>
      <Table
        onSort={handleSort}
        data={getTableData().data}
        sortColumn={sortBy}
        columns={columns}
        selectedRows={selectedRows}
      />
      <Pagination
        itemsCount={getTableData().length}
        pageSize={pageSize}
        currentPage={currentPage}
        onPageChange={handlePageChange}
      />
      <DeleteModal
        show={showDeleteModal}
        handleAccept={handleDelete}
        onClose={onCloseDeleteModal}
        selectedRows={selectedRows}
      />
      <MergeModal
        show={showMergeModal}
        handleAccept={handleMerge}
        onClose={onCloseMergeModal}
        selectedRows={selectedRows}
      />
    </>
  );
}

List.propTypes = {
  data: PropTypes.arrayOf(
    PropTypes.shape({ name: PropTypes.string, id: PropTypes.number })
  ).isRequired,
  columns: ColumnProptypes.isRequired,
  selectedRows: SelectedRowsProptypes.isRequired,
};
export default List;
