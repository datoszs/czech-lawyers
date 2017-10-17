import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import {wrapLinkMouseEvent} from '../../util';
import styles from './Statement.less';

const Statement = ({children, onClick, href}) => (
    <a
        href={href}
        onClick={wrapLinkMouseEvent(onClick)}
        className={classNames(styles.main, 'well')}
    >
        {children}
    </a>
);

Statement.propTypes = {
    children: PropTypes.node.isRequired,
    onClick: PropTypes.func.isRequired,
    href: PropTypes.string.isRequired,
};

export default Statement;
