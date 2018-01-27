import React from 'react';
import PropTypes from 'prop-types';
import {Helmet} from 'react-helmet';

const PageTitle = ({children}) => <Helmet><title>{children}</title></Helmet>;

PageTitle.propTypes = {
    children: PropTypes.string.isRequired,
};

export default PageTitle;
