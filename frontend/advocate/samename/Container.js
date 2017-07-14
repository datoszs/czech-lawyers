import React from 'react';
import PropTypes from 'prop-types';
import {List} from 'immutable';
import {Alert} from 'react-bootstrap';
import {connect} from 'react-redux';
import {If} from '../../util';
import {RichText, RouterLink} from '../../containers';
import {ADVOCATE_DETAIL} from '../../routes';
import {getIds} from './selectors';
import Advocate from './SameNameAdvocate';

const SameNameAdvocateContainer = ({ids}) => (
    <Alert bsStyle="warning">
        <RichText msg="advocate.same.name.warning" />
        <ul>
            {ids.map((id) => <li key={id}><RouterLink route={ADVOCATE_DETAIL} params={{id}}><Advocate id={id} /></RouterLink></li>)}
        </ul>
    </Alert>
);

SameNameAdvocateContainer.propTypes = {
    ids: PropTypes.instanceOf(List).isRequired,
};

const mapStateToProps = (state) => ({
    ids: getIds(state),
});

const mergeProps = ({ids}) => ({
    test: ids.size > 0,
    Component: SameNameAdvocateContainer,
    ids,
});

export default connect(mapStateToProps, undefined, mergeProps)(If);
