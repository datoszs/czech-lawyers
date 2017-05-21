import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {List} from 'immutable';
import {ListGroup} from 'react-bootstrap';
import AutocompleteItem from './AutocompleteItem';
import {getResultIds} from './selectors';

const AutocompleteListComponent = ({ids}) => (
    <ListGroup>
        {ids.map((id) => <AutocompleteItem key={id} id={id} />)}
    </ListGroup>
);

AutocompleteListComponent.propTypes = {
    ids: PropTypes.instanceOf(List).isRequired,
};

const mapStateToProps = (state) => ({
    ids: getResultIds(state),
});

export default connect(mapStateToProps)(AutocompleteListComponent);
