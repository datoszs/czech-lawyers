import React from 'react';
import PropTypes from 'prop-types';
import styles from './AutocompleteLayout.css';

const AutocompleteLayout = ({input, list}) => (
    <div className={styles.autocomplete}>
        {input}
        <div className={styles.autocompleteList}>{list}</div>
    </div>
);

AutocompleteLayout.propTypes = {
    input: PropTypes.element.isRequired,
    list: PropTypes.element.isRequired,
};

export default AutocompleteLayout;
