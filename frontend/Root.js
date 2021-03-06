import React from 'react';
import {Provider} from 'react-redux';

import './include.less';

import AppRouter from './AppRouter';

// eslint-disable-next-line react/prop-types, #scaffolding
const Root = ({store}) => (
    <Provider store={store}>
        <AppRouter />
    </Provider>
);

export default Root;
