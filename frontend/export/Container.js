import React from 'react';
import {PageHeader, Row, Col, Panel, Button} from 'react-bootstrap';
import {Msg, PageTitle, RichText} from '../containers';

export default () => (
    <section>
        <PageTitle msg="export.title" />
        <PageHeader><Msg msg="export.title" /></PageHeader>
        <Row>
            <Col sm={6}>
                <Panel bsStyle="primary" header={<Msg msg="export.main.title" />}>
                    <RichText msg="export.main.text" />
                    <Button bsStyle="primary" href="/api/download-export">Stáhnout</Button>
                </Panel>
            </Col>
            <Col sm={6}>
                <RichText msg="export.legend" />
            </Col>
        </Row>
    </section>
);
