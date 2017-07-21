import React from 'react';
import PropTypes from 'prop-types';
import {Glyphicon} from 'react-bootstrap';

const SearchDisclaimer = ({children}) => (
    <div className="search-disclaimer">
        <Glyphicon glyph="info-sign" className="info-sign" />
        <div className="legend">{children}</div>
    </div>
);

SearchDisclaimer.propTypes = {
    children: PropTypes.node.isRequired,
};

export default SearchDisclaimer;
