import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Glyphicon, Row, Col} from 'react-bootstrap';
import moment from 'moment';
import translate from '../translate';
import {DetailPanel} from '../components';
import {Msg} from '../containers';
import {getDocument} from './selectors';

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

const mergeProps = ({mark, date, href, dateFormat}, {openDetail}) => {
    const formattedDate = moment(date).format(dateFormat);
    return ({
        title: mark,
        footer: href ? formattedDate : (
            <Row>
                <Col md={3}>{formattedDate}</Col>
                <Col md={9}><Glyphicon glyph="alert" /> <Msg msg="case.document.missing" /></Col>
            </Row>
        ),
        onClick: href && openDetail(href),
        href,
    });
};

const DocumentDetail = connect(mapStateToProps, mapDispatchToProps, mergeProps)(DetailPanel);

DocumentDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default DocumentDetail;
