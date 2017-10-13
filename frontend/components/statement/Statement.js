import React from 'react';
import PropTypes from 'prop-types';
import {Well} from 'react-bootstrap';
import styles from './Statement.css';

const Statement = ({children, onClick}) => (
    <Well onClick={onClick} className={styles.main}>
        {children}
    </Well>
);

Statement.propTypes = {
    children: PropTypes.node.isRequired,
    onClick: PropTypes.func.isRequired,
};

export default Statement;
