import React, {PropTypes} from 'react';

import Navigation from './Navigation';
import Sidebar from './Sidebar';

const AppContainer = ({children}) => (
    <div>
        <Navigation />
        {children}
        <Sidebar />
    </div>
);

AppContainer.propTypes = {
    children: PropTypes.node,
};

AppContainer.defaultProps = {
    children: null,
};

export default AppContainer;
