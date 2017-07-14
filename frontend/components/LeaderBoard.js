import React from 'react';
import PropTypes from 'prop-types';
import {Panel, ListGroup} from 'react-bootstrap';

const LeaderBoard = ({children, type}) => (
    <Panel className={`leader-board ${type}`}>
        <ListGroup fill>
            {children}
        </ListGroup>
    </Panel>
);

LeaderBoard.propTypes = {
    children: PropTypes.node.isRequired,
    type: PropTypes.oneOf(['positive', 'negative']).isRequired,
};

export default LeaderBoard;
