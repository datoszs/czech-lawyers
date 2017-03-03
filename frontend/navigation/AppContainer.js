import React, {PropTypes} from 'react';

import Navigation from './Navigation';
import Sidebar from './Sidebar';
import Footer from './Footer';

const AppContainer = ({children}) => (
    <div>
        <Navigation />
        <div className="container">
            {children}
        </div>
        <Sidebar />
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
