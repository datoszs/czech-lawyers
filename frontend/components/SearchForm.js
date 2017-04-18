import React, {PropTypes} from 'react';
import {wrapEventStop} from '../util';

const SearchForm = ({onSubmit, children}) => (
    <form className="search-form" onSubmit={wrapEventStop(onSubmit)}>
        {children}
    </form>
);

SearchForm.propTypes = {
    onSubmit: PropTypes.func.isRequired,
    children: PropTypes.element.isRequired,
};

export default SearchForm;
