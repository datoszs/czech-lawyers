import React from 'react';
import {Provider} from 'react-redux';

import './include.less';
import './index.less';

import Router from './Router';

// eslint-disable-next-line react/prop-types, #scaffolding
const Root = ({store}) => (
    <Provider store={store}>
        <Router />
    </Provider>
);

export default Root;
