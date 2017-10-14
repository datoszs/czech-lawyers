import React from 'react';
import PropTypes from 'prop-types';
import {Panel, ListGroup} from 'react-bootstrap';
import styles from './LeaderBoard.less';

const styleMap = {
    positive: styles.positive,
    negative: styles.negative,
};

const LeaderBoard = ({children, type}) => (
    <Panel className={styleMap[type]}>
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
