import React, {PropTypes} from 'react';

import Navigation from './Navigation';

const AppContainer = ({children}) => (
    <div>
        <Navigation />
        {children}
    </div>
);

AppContainer.propTypes = {
    children: PropTypes.node,
};

AppContainer.defaultProps = {
    children: null,
};

export default AppContainer;
