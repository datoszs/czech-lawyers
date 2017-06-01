import React from 'react';
import PropTypes from 'prop-types';
import {Panel, ListGroup} from 'react-bootstrap';

const LeaderBoard = ({header, children, type}) => (
    <Panel header={<p>{header}</p>} className={`leader-board ${type}`}>
        <ListGroup fill>
            {children}
        </ListGroup>
    </Panel>
);

LeaderBoard.propTypes = {
    header: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
    type: PropTypes.oneOf(['positive', 'negative']).isRequired,
};

export default LeaderBoard;
