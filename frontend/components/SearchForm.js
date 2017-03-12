import React, {PropTypes} from 'react';
import {Button} from 'react-bootstrap';
import {wrapEventStop} from '../util';

const SearchForm = ({onSubmit, msgSubmit, children}) => (
    <form className="search-form" onSubmit={wrapEventStop(onSubmit)}>
        {children}
        <Button type="submit" bsStyle="primary">{msgSubmit}</Button>
    </form>
);

SearchForm.propTypes = {
    onSubmit: PropTypes.func.isRequired,
    msgSubmit: PropTypes.string.isRequired,
    children: PropTypes.element.isRequired,
};

export default SearchForm;
