import React from 'react';
import PropTypes from 'prop-types';
import {Glyphicon} from 'react-bootstrap';
import styles from './SearchDisclaimer.less';

const SearchDisclaimer = ({children}) => (
    <div className={styles.main}>
        <Glyphicon glyph="info-sign" className={styles.info} />
        <div className={styles.legend}>{children}</div>
    </div>
);

SearchDisclaimer.propTypes = {
    children: PropTypes.node.isRequired,
};

export default SearchDisclaimer;
