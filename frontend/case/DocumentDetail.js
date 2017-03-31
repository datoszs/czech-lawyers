import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import moment from 'moment';
import translate from '../translate';
import {DetailPanel} from '../components';
import {getDocument} from './selectors';

const DocumentDetailComponent = ({mark, date, handleDetail, dateFormat}) => (
    <DetailPanel
        title={mark}
        onClick={handleDetail}
        footer={moment(date).format(dateFormat)}
    />
);

DocumentDetailComponent.propTypes = {
    mark: PropTypes.string.isRequired,
    date: PropTypes.number.isRequired,
    handleDetail: PropTypes.func.isRequired,
    dateFormat: PropTypes.string.isRequired,
};

const mapStateToProps = (state, {id}) => {
    const document = getDocument(state, id);
    return {
        mark: document.mark,
        date: document.date,
        href: document.link,
        dateFormat: translate.getShortDateFormat(state),
    };
};

const mapDispatchToProps = () => ({
    openDetail: (href) => () => window.open(href),
});

const mergeProps = ({mark, date, href, dateFormat}, {openDetail}) => ({
    mark,
    date,
    dateFormat,
    handleDetail: openDetail(href),
});

const DocumentDetail = connect(mapStateToProps, mapDispatchToProps, mergeProps)(DocumentDetailComponent);

DocumentDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default DocumentDetail;
