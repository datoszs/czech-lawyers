import React from 'react';
import PropTypes from 'prop-types';
import {wrapEventStop} from '../util';
import styles from './SearchForm.css';

const SearchForm = ({onSubmit, children}) => (
    <form className={styles.main} onSubmit={wrapEventStop(onSubmit)}>
        {children}
    </form>
);

SearchForm.propTypes = {
    onSubmit: PropTypes.func.isRequired,
    children: PropTypes.element.isRequired,
};

export default SearchForm;
