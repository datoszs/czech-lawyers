import PropTypes from 'prop-types';
import {ListGroupItem} from 'react-bootstrap';
import {connect} from 'react-redux';
import {getResult} from './selectors';

const mapStateToProps = (state, {id}) => ({
    children: getResult(state, id).name,
});

const AutocompleteItem = connect(mapStateToProps)(ListGroupItem);

AutocompleteItem.propTypes = {
    id: PropTypes.number.isRequired,
};

export default AutocompleteItem;
