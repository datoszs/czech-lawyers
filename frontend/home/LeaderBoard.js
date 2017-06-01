import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {List} from 'immutable';
import {ListGroup, Panel} from 'react-bootstrap';
import translate from '../translate';
import LeaderBoardAdvocate from './LeaderBoardAdvocate';

const LeaderBoardComponent = ({ids, header, bsStyle}) => (
    <Panel header={header} bsStyle={bsStyle}>
        <ListGroup fill>
            {ids.map((id) => <LeaderBoardAdvocate key={id} id={id} />)}
        </ListGroup>
    </Panel>
);

LeaderBoardComponent.propTypes = {
    ids: PropTypes.instanceOf(List).isRequired,
    header: PropTypes.string.isRequired,
    bsStyle: PropTypes.string.isRequired,
};

const mapStateToProps = (state, {selector, msg}) => ({
    ids: selector(state),
    header: translate.getMessage(state, msg),
});

const LeaderBoard = connect(mapStateToProps)(LeaderBoardComponent);

LeaderBoard.propTypes = {
    selector: PropTypes.func.isRequired,
    msg: PropTypes.string.isRequired,
    bsStyle: PropTypes.string.isRequired,
};

export default LeaderBoard;
