import React from 'react';
import PropTypes from 'prop-types';

import Navigation from './Navigation';
import Sidebar from './Sidebar';
import Footer from './Footer';
import Redirect from './Redirect';

const AppContainer = ({children}) => (
    <div>
        <Navigation />
        <div className="container">
            {children}
        </div>
        <Sidebar />
        <Redirect />
        <Footer />
    </div>
);

AppContainer.propTypes = {
    children: PropTypes.node,
};

AppContainer.defaultProps = {
    children: null,
};

export default AppContainer;
