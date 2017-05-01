import React from 'react';
import PropTypes from 'prop-types';

const AutocompleteLayout = ({input, list}) => (
    <div className="autocomplete">
        {input}
        <div className="autocomplete-list">{list}</div>
    </div>
);

AutocompleteLayout.propTypes = {
    input: PropTypes.element.isRequired,
    list: PropTypes.element.isRequired,
};

export default AutocompleteLayout;
