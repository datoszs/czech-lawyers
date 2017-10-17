import React from 'react';
import PropTypes from 'prop-types';
import styles from './AutocompleteLayout.less';

const AutocompleteLayout = ({input, list}) => (
    <div className={styles.main}>
        {input}
        <div className={styles.list}>{list}</div>
    </div>
);

AutocompleteLayout.propTypes = {
    input: PropTypes.element.isRequired,
    list: PropTypes.element.isRequired,
};

export default AutocompleteLayout;
