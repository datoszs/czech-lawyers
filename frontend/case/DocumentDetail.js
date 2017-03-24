import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import moment from 'moment';
import {DetailPanel} from '../components';
import {getDocument} from './selectors';

const DocumentDetailComponent = ({mark, date, handleDetail}) => (
    <DetailPanel
        title={mark}
        onClick={handleDetail}
        footer={moment(date).format('LL')}
    />
);

DocumentDetailComponent.propTypes = {
    mark: PropTypes.string.isRequired,
    date: PropTypes.number.isRequired,
    handleDetail: PropTypes.func.isRequired,
};

const mapStateToProps = (state, {id}) => {
    const document = getDocument(state, id);
    return {
        mark: document.mark,
        date: document.date,
        href: document.link,
    };
};

const mapDispatchToProps = () => ({
    openDetail: (href) => () => window.open(href),
});

const mergeProps = ({mark, date, href}, {openDetail}) => ({
    mark,
    date,
    handleDetail: openDetail(href),
});

const DocumentDetail = connect(mapStateToProps, mapDispatchToProps, mergeProps)(DocumentDetailComponent);

DocumentDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default DocumentDetail;
