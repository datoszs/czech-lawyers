import React from 'react';
import {connect} from 'react-redux';
import {ThreeColumn} from '../components';
import {search} from './modules';
import CaseDetail from './CaseDetail';

const mapStateToProps = (state) => ({
    ids: search.getIds(state),
});

const mergeProps = ({ids}) => ({
    children: ids.map((id) => <CaseDetail key={id} id={id} />),
});

export default connect(mapStateToProps, undefined, mergeProps)(ThreeColumn);
